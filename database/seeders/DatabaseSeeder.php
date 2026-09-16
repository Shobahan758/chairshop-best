<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CouponSeeder::class);

        if (config('auth.seed_admin.email') && config('auth.seed_admin.password')) {
            User::updateOrCreate(['email' => config('auth.seed_admin.email')], [
                'name' => 'ChairGhor Super Admin',
                'password' => config('auth.seed_admin.password'),
                'is_admin' => true,
                'role' => 'super_admin',
            ]);
        }

        $categoryData = ['office' => ['অফিস চেয়ার', 'briefcase'], 'gaming' => ['গেমিং চেয়ার', 'controller'], 'dining' => ['ডাইনিং চেয়ার', 'cup-hot'], 'recliner' => ['রিক্লাইনার', 'lamp'], 'study' => ['স্টাডি চেয়ার', 'book'], 'wooden' => ['কাঠের চেয়ার', 'tree']];
        $categories = collect($categoryData)->mapWithKeys(function (array $category, string $slug): array {
            $model = Category::updateOrCreate(['slug' => $slug], ['name' => $category[0], 'icon' => $category[1]]);

            return [$slug => $model];
        });

        $products = [
            ['office', 'অরা প্রো এরগোনমিক চেয়ার', 'aura-pro-ergonomic-chair', 18500, 15500, 'কালো', 'মেশ ফ্যাব্রিক', 'photo-1580480055273-228ff5388ef8'],
            ['office', 'নোভা এক্সিকিউটিভ চেয়ার', 'nova-executive-chair', 26500, 23900, 'কফি', 'প্রিমিয়াম লেদার', 'photo-1505797149-43b0069ec26b'],
            ['office', 'এয়ারফ্লো মেশ চেয়ার', 'airflow-mesh-chair', 16900, 14900, 'ধূসর', 'ব্রিদেবল মেশ', 'photo-1551298370-9d3d53740c72'],
            ['office', 'ডিরেক্টর লাক্স চেয়ার', 'director-lux-chair', 32500, 28900, 'বাদামি', 'জেনুইন লেদার', 'photo-1493663284031-b7e3aefcae8e'],
            ['gaming', 'ভরটেক্স জি১ গেমিং চেয়ার', 'vortex-g1-gaming-chair', 22000, 18900, 'লাল-কালো', 'PU লেদার', 'photo-1598550476439-6847785fcea6'],
            ['gaming', 'নিও রেসার এলিট', 'neo-racer-elite', 28500, 25500, 'নীল', 'মেমোরি ফোম', 'photo-1586953208448-b95a79798f07'],
            ['gaming', 'ফ্যান্টম আরজিবি চেয়ার', 'phantom-rgb-chair', 31000, 27900, 'কালো', 'কোল্ড কিউর ফোম', 'photo-1616486338812-3dadae4b4ace'],
            ['gaming', 'টাইটান প্রো গেমিং চেয়ার', 'titan-pro-gaming-chair', 34500, 31500, 'কালো-সোনালি', 'PU লেদার', 'photo-1592078615290-033ee584e267'],
            ['dining', 'নিরা ডাইনিং চেয়ার', 'nira-dining-chair', 9500, null, 'বেইজ', 'সলিড উড', 'photo-1567538096630-e0c55bd6374c'],
            ['dining', 'এলম স্ক্যান্ডি চেয়ার', 'elm-scandi-chair', 11200, 9900, 'প্রাকৃতিক', 'অ্যাশ উড', 'photo-1503602642458-232111445657'],
            ['dining', 'ভেলভেট ডাইনিং চেয়ার', 'velvet-dining-chair', 13800, 11900, 'সবুজ', 'ভেলভেট', 'photo-1586023492125-27b2c045efd7'],
            ['dining', 'ক্যান ডাইনিং চেয়ার', 'cane-dining-chair', 14900, null, 'ওক', 'বেত ও কাঠ', 'photo-1549497538-303791108f95'],
            ['recliner', 'অবসর লাক্স রিক্লাইনার', 'oboshor-lux-recliner', 42000, 38500, 'ওয়ালনাট', 'ফ্যাব্রিক', 'photo-1618220179428-22790b461013'],
            ['recliner', 'রিল্যাক্স পাওয়ার রিক্লাইনার', 'relax-power-recliner', 58000, 52900, 'চকলেট', 'লেদার', 'photo-1555041469-a586c61ea9bc'],
            ['recliner', 'ক্লাউড রকিং রিক্লাইনার', 'cloud-rocking-recliner', 46500, 42900, 'ক্রিম', 'বুকলে ফ্যাব্রিক', 'photo-1567016432779-094069958ea5'],
            ['recliner', 'সিনেমা ডুয়াল রিক্লাইনার', 'cinema-dual-recliner', 72000, 67500, 'কালো', 'প্রিমিয়াম লেদার', 'photo-1550226891-ef816aed4a98'],
            ['study', 'ফোকাস কমপ্যাক্ট চেয়ার', 'focus-compact-chair', 7800, 6900, 'সবুজ', 'মোল্ডেড ফোম', 'photo-1519947486511-46149fa0a254'],
            ['study', 'স্কলার স্টাডি চেয়ার', 'scholar-study-chair', 8900, 7900, 'নীল', 'মেশ', 'photo-1579656592043-a20e25a4aa4b'],
            ['study', 'স্মার্ট টাস্ক চেয়ার', 'smart-task-chair', 10500, 9200, 'কালো', 'ফ্যাব্রিক', 'photo-1493663284031-b7e3aefcae8e'],
            ['study', 'জুনিয়র কমফোর্ট চেয়ার', 'junior-comfort-chair', 6500, 5900, 'হলুদ', 'মোল্ডেড প্লাস্টিক', 'photo-1538688525198-9b88f6f53126'],
            ['wooden', 'ঐতিহ্য কাঠের চেয়ার', 'oitijjho-wooden-chair', 12500, null, 'প্রাকৃতিক', 'মেহগনি কাঠ', 'photo-1595428774223-ef52624120d2'],
            ['wooden', 'নকশি আর্ম চেয়ার', 'nakshi-arm-chair', 17800, 15900, 'গাঢ় বাদামি', 'সেগুন কাঠ', 'photo-1532323544230-7191fd51bc1b'],
            ['wooden', 'মিনিমাল ওক চেয়ার', 'minimal-oak-chair', 14500, 12900, 'হালকা ওক', 'ওক কাঠ', 'photo-1551298370-9d3d53740c72'],
            ['wooden', 'বেঙ্গল ক্যান চেয়ার', 'bengal-cane-chair', 16500, 14900, 'প্রাকৃতিক', 'বেত ও সেগুন', 'photo-1598300042247-d088f8ab3a91'],
        ];

        $galleryPhotos = [
            'photo-1503602642458-232111445657',
            'photo-1567538096630-e0c55bd6374c',
            'photo-1586023492125-27b2c045efd7',
            'photo-1592078615290-033ee584e267',
            'photo-1598300042247-d088f8ab3a91',
            'photo-1616486338812-3dadae4b4ace',
            'photo-1551298370-9d3d53740c72',
            'photo-1493663284031-b7e3aefcae8e',
        ];

        foreach ($products as $index => $product) {
            Product::updateOrCreate(['sku' => 'CG-'.str_pad($index + 1, 3, '0', STR_PAD_LEFT)], [
                'category_id' => $categories[$product[0]]->id,
                'name' => $product[1],
                'slug' => $product[2],
                'price' => $product[3],
                'sale_price' => $product[4],
                'color' => $product[5],
                'material' => $product[6],
                'image' => 'https://images.unsplash.com/'.$product[7].'?auto=format&fit=crop&w=900&q=85',
                'additional_images' => collect(range(1, 4))->map(function (int $offset) use ($galleryPhotos, $index): string {
                    $photo = $galleryPhotos[($index + $offset) % count($galleryPhotos)];

                    return 'https://images.unsplash.com/'.$photo.'?auto=format&fit=crop&w=900&q=85';
                })->all(),
                'short_description' => 'দীর্ঘসময় আরাম ও সঠিক বসার ভঙ্গির জন্য যত্নে তৈরি।',
                'description' => 'শক্তিশালী গঠন, আরামদায়ক সাপোর্ট এবং আধুনিক ঘরের সঙ্গে মানানসই নকশা। দক্ষ কারিগরের হাতে তৈরি এই চেয়ারটি কাজ ও বিশ্রাম—দুই জায়গাতেই ভরসার সঙ্গী।',
                'stock' => 10 + $index,
                'featured' => $index < 8,
                'just_for_you' => $index >= 8 && $index < 12,
                'office_essential' => $product[0] === 'office',
                'gaming_pick' => $product[0] === 'gaming',
                'is_active' => true,
            ]);
        }
    }
}
