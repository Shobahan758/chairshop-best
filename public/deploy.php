<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('Content-Type: text/plain; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed');
}

$projectPath = dirname(__DIR__);
$secretPath = dirname($projectPath).'/.chairshop-deploy-secret';
$secret = is_readable($secretPath) ? trim((string) file_get_contents($secretPath)) : '';

if (strlen($secret) < 32) {
    error_log('Deploy webhook secret is missing or too short.');
    http_response_code(503);
    exit('Deployment is not configured');
}

$payload = file_get_contents('php://input', false, null, 0, 1_048_577);

if ($payload === false || strlen($payload) > 1_048_576) {
    http_response_code(413);
    exit('Payload too large');
}

$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$expectedSignature = 'sha256='.hash_hmac('sha256', $payload, $secret);

if (! is_string($signature) || ! hash_equals($expectedSignature, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}

if (($_SERVER['HTTP_X_GITHUB_EVENT'] ?? '') !== 'push') {
    http_response_code(202);
    exit('Event ignored');
}

try {
    $event = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    http_response_code(400);
    exit('Invalid JSON');
}

if (! is_array($event)
    || ($event['repository']['full_name'] ?? null) !== 'Shobahan758/chairshop-best'
    || ($event['ref'] ?? null) !== 'refs/heads/master'
    || ($event['deleted'] ?? false) === true) {
    http_response_code(202);
    exit('Push ignored');
}

if (! function_exists('proc_open')) {
    error_log('Deploy webhook cannot run commands: proc_open is disabled.');
    http_response_code(503);
    exit('Deployment commands are unavailable');
}

$lock = @fopen($projectPath.'/storage/framework/deploy.lock', 'c');

if ($lock === false) {
    error_log('Deploy webhook could not open its lock file.');
    http_response_code(503);
    exit('Deployment lock is unavailable');
}

if (! flock($lock, LOCK_EX | LOCK_NB)) {
    fclose($lock);
    http_response_code(409);
    exit('Deployment already running');
}

if (function_exists('set_time_limit')) {
    set_time_limit(120);
}

if (function_exists('putenv')) {
    putenv('GIT_TERMINAL_PROMPT=0');
    putenv('GIT_SSH_COMMAND=ssh -o BatchMode=yes -o ConnectTimeout=10');
}

foreach ([
    ['git', 'pull', '--ff-only', 'origin', 'master'],
    ['php', 'artisan', 'optimize:clear'],
] as $command) {
    $logPath = $projectPath.'/storage/logs/deploy.log';
    $process = @proc_open($command, [
        0 => ['file', '/dev/null', 'r'],
        1 => ['file', $logPath, 'a'],
        2 => ['file', $logPath, 'a'],
    ], $pipes, $projectPath);

    if (! is_resource($process)) {
        error_log('Deploy webhook could not start '.$command[0].'.');
        http_response_code(500);
        exit('Deployment failed');
    }

    if (proc_close($process) !== 0) {
        error_log('Deploy command failed: '.implode(' ', $command).'. Check '.$logPath.'.');
        http_response_code(500);
        exit('Deployment failed; check the PHP error log');
    }
}

flock($lock, LOCK_UN);
fclose($lock);

exit('Deployment complete');
