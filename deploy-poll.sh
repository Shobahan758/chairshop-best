#!/bin/sh

set -eu

PATH=/usr/local/cpanel/3rdparty/lib/path-bin:/usr/local/bin:/usr/bin:/bin
GIT_TERMINAL_PROMPT=0
GIT_SSH_COMMAND='ssh -o BatchMode=yes -o ConnectTimeout=10'
export PATH GIT_TERMINAL_PROMPT GIT_SSH_COMMAND

cd "$(dirname "$0")"

exec 9>storage/framework/deploy.lock
/usr/bin/flock -n 9 || exit 0

current_commit=$(git rev-parse HEAD)
git fetch --quiet origin master
incoming_commit=$(git rev-parse FETCH_HEAD)

if [ "$current_commit" = "$incoming_commit" ]; then
    exit 0
fi

git merge --ff-only FETCH_HEAD
php artisan optimize:clear --no-interaction
