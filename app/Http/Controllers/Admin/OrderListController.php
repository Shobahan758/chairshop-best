<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFakeOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\IncompleteOrder;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderListController extends Controller
{
    public function index(?string $filter = null): View
    {
        $statuses = match ($filter) {
            'new' => ['অর্ডার গ্রহণ'],
            'processing' => ['নিশ্চিত', 'প্রস্তুত', 'পাঠানো'],
            'shipping' => ['পাঠানো'],
            'completed' => ['ডেলিভারি সম্পন্ন'],
            'cancelled' => ['বাতিল'],
            default => [],
        };
        $title = match ($filter) {
            'new' => 'New Orders',
            'processing' => 'Processing',
            'shipping' => 'Shipping',
            'completed' => 'Completed Orders',
            'cancelled' => 'Cancelled Orders',
            default => 'All Orders',
        };
        $orders = Order::query()->with('items')->where('is_fake', false)
            ->when($statuses, fn ($query) => $query->whereIn('status', $statuses))
            ->latest()->orderByDesc('id')->paginate(20);

        return view('admin.orders.index', compact('orders', 'title'));
    }

    public function edit(Order $order): View
    {
        abort_if($order->is_fake, 404);

        return view('admin.orders.edit', ['order' => $order->load('items')]);
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        abort_if($order->is_fake, 404);
        $order->update($request->validated());

        return redirect()->route('admin.orders.index')->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        abort_if($order->is_fake, 404);
        $order->delete();

        return back()->with('success', 'Order deleted successfully.');
    }

    public function incomplete(bool $today = false): View
    {
        $orders = IncompleteOrder::query()->when($today, fn ($query) => $query->whereToday('updated_at'))->latest('updated_at')->paginate(20);

        return view('admin.orders.incomplete', compact('orders', 'today'));
    }

    public function incompleteToday(): View
    {
        return $this->incomplete(true);
    }

    public function destroyIncomplete(IncompleteOrder $incompleteOrder): RedirectResponse
    {
        $incompleteOrder->delete();

        return back()->with('success', 'Incomplete order removed.');
    }

    public function fake(bool $today = false): View
    {
        $orders = Order::query()->with('items')->where('is_fake', true)->when($today, fn ($query) => $query->whereToday('created_at'))->latest()->paginate(20);

        return view('admin.orders.fake', compact('orders', 'today'));
    }

    public function fakeToday(): View
    {
        return $this->fake(true);
    }

    public function createFake(): View
    {
        return view('admin.orders.create-fake', ['products' => Product::query()->orderBy('name')->get()]);
    }

    public function storeFake(StoreFakeOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);
        $subtotal = (float) $product->current_price * $data['quantity'];
        $order = Order::create([
            ...$data,
            'order_number' => 'FAKE-'.now()->format('ymd').'-'.strtoupper(str()->random(5)),
            'payment_method' => 'cod',
            'subtotal' => $subtotal,
            'discount' => 0,
            'delivery_charge' => 0,
            'total' => $subtotal,
            'is_fake' => true,
        ]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $data['quantity'], 'price' => $product->current_price]);

        return redirect()->route('admin.fake-orders.index')->with('success', 'Fake order created successfully.');
    }

    public function destroyFake(Order $order): RedirectResponse
    {
        abort_unless($order->is_fake, 404);
        $order->delete();

        return back()->with('success', 'Fake order removed.');
    }
}
