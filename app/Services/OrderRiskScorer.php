<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderRiskScorer
{
    /**
     * @return array{risk_score: int, risk_reasons: array<string, int>, is_fake: bool}
     */
    public function assess(string $phone, string $name, string $address, string $area, string $district, ?string $ip): array
    {
        $reasons = [];
        $previous = Order::where('phone', $phone)->latest()->orderByDesc('id')->first();

        if ($previous) {
            $reasons['repeated_phone'] = 40;
        }
        if ($ip && Order::where('source_ip', $ip)->where('created_at', '>=', now()->startOfSecond()->subMinutes(30))->exists()) {
            $reasons['repeated_ip'] = 25;
        }
        if ($previous && $previous->created_at->greaterThanOrEqualTo(now()->startOfSecond()->subMinutes(2))) {
            $reasons['rapid_repeat'] = 15;
        }
        if ($previous && (
            $this->normalize($previous->name) !== $this->normalize($name)
            || $this->normalize($previous->address) !== $this->normalize($address)
            || $this->normalize($previous->area) !== $this->normalize($area)
            || $this->normalize($previous->district) !== $this->normalize($district)
        )) {
            $reasons['changed_details'] = 20;
        }
        if (Order::where('phone', $phone)->where('is_fake', true)->exists()) {
            $reasons['previous_fake_phone'] = 100;
        }

        $score = array_sum($reasons);

        return ['risk_score' => $score, 'risk_reasons' => $reasons, 'is_fake' => $score >= config('order_risk.fake_threshold', 60)];
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::squish($value));
    }
}
