<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\DashboardReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request, DashboardReport $report): View
    {
        if (! in_array('dashboard', $request->user()->staffPermissions(), true)) {
            return view('admin.staff-home');
        }

        $dates = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $start = Carbon::parse($dates['from'] ?? now()->subDays(29)->toDateString())->startOfDay();
        $end = Carbon::parse($dates['to'] ?? now()->toDateString())->endOfDay();
        if ($start->gt($end) || $start->diffInDays($end) > 366) {
            throw ValidationException::withMessages(['from' => 'Choose a date range of up to one year, with the start before the end.']);
        }

        return view('admin.dashboard', $report->build($start, $end));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate(['status' => 'required|in:অর্ডার গ্রহণ,নিশ্চিত,প্রস্তুত,পাঠানো,ডেলিভারি সম্পন্ন,বাতিল']);
        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Order status updated successfully.');
    }
}
