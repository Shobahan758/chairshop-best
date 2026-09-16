<div class="text-nowrap"><b>Risk: {{ $order->risk_score ?? 0 }}</b></div>
@if($order->risk_reasons)
    <details><summary>কারণ দেখুন</summary>
        @foreach($order->risk_reasons as $reason => $points)
            <div class="small">{{ config('order_risk.reason_labels.'.$reason, $reason) }}: +{{ $points }}</div>
        @endforeach
    </details>
@endif
