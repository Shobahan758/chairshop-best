<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function home()
    {
        return view('home', [
            'categories' => Category::where('is_active', true)->withCount(['products' => fn ($query) => $query->where('is_active', true)])->get(),
            'featured' => Product::with('category')->where('is_active', true)->whereHas('category', fn ($query) => $query->where('is_active', true))->where('featured', true)->latest()->take(8)->get(),
            'justForYou' => Product::with('category')->where('is_active', true)->whereHas('category', fn ($query) => $query->where('is_active', true))->where('just_for_you', true)->latest()->take(4)->get(),
            'officeProducts' => Product::with('category')->where('is_active', true)->whereHas('category', fn ($query) => $query->where('is_active', true))->where('office_essential', true)->latest()->take(4)->get(),
            'gamingProducts' => Product::with('category')->where('is_active', true)->whereHas('category', fn ($query) => $query->where('is_active', true))->where('gaming_pick', true)->latest()->take(4)->get(),
        ]);
    }

    public function shop(Request $r)
    {
        $q = Product::with('category')->where('is_active', true)->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));
        if ($r->filled('q')) {
            $q->where('name', 'like', '%'.$r->q.'%');
        } if ($r->filled('category')) {
            $q->whereHas('category', fn ($x) => $x->where('slug', $r->category));
        } if ($r->sort === 'low') {
            $q->orderByRaw('COALESCE(sale_price,price) asc');
        } elseif ($r->sort === 'high') {
            $q->orderByRaw('COALESCE(sale_price,price) desc');
        } else {
            $q->latest();
        }

        return view('shop', ['products' => $q->paginate(12)->withQueryString(), 'categories' => Category::where('is_active', true)->get()]);
    }

    public function product(Product $product)
    {
        abort_unless($product->is_active && $product->category?->is_active, 404);

        return view('product', [
            'product' => $product->load(['category', 'reviews' => fn ($query) => $query->latest()])->loadAvg('reviews', 'rating'),
            'related' => Product::where('is_active', true)->where('category_id', $product->category_id)->whereKeyNot($product->id)->take(4)->get(),
        ]);
    }

    public function track(Request $r)
    {
        $order = null;
        if ($r->filled(['order_number', 'phone'])) {
            $order = Order::where('order_number', $r->order_number)->where('phone', $r->phone)->where('is_fake', false)->with('items')->first();
        }

        return view('track', compact('order'));
    }

    public function contact(): View
    {
        return view('contact', ['settings' => GeneralSetting::first() ?? new GeneralSetting]);
    }

    public function page(string $slug)
    {
        if ($slug === 'contact') {
            return $this->contact();
        }

        $pages = ['about' => ['আমাদের গল্প', 'চেয়ারঘর বাংলাদেশের ঘর ও কর্মস্থলে আরাম, স্বাস্থ্য এবং সুন্দর নকশা পৌঁছে দেওয়ার একটি বিশ্বস্ত উদ্যোগ।'], 'contact' => ['যোগাযোগ করুন', 'সহায়তা: ০১৭০০-০০০০০০ · ইমেইল: hello@chairghor.bd · শোরুম: ঢাকা, বাংলাদেশ'], 'delivery' => ['ডেলিভারি নীতিমালা', 'ঢাকার ভেতরে ২–৩ এবং ঢাকার বাইরে ৩–৫ কর্মদিবসে পণ্য পৌঁছে দেওয়ার চেষ্টা করি।'], 'returns' => ['রিটার্ন ও ওয়ারেন্টি', 'পণ্য গ্রহণের সময় পরীক্ষা করুন। উৎপাদনজনিত ত্রুটিতে ৭ দিনের সহজ রিটার্ন এবং পণ্যভেদে ওয়ারেন্টি রয়েছে।']];
        abort_unless(isset($pages[$slug]), 404);

        return view('page', ['title' => $pages[$slug][0], 'content' => $pages[$slug][1], 'slug' => $slug]);
    }
}
