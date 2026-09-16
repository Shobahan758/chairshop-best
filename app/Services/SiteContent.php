<?php

namespace App\Services;

use App\Models\GeneralSetting;

class SiteContent
{
    /** @param array<string, array<string, array<string, string|null>>> $values */
    public function __construct(private array $values = [], private ?GeneralSetting $settings = null) {}

    public function text(string $page, string $section, string $field): string
    {
        $definition = config("site_content.$page.$section.$field", []);
        $setting = $definition['general_setting'] ?? null;

        return $this->values[$page][$section][$field]
            ?? ($setting ? $this->settings?->getAttribute($setting) : null)
            ?? ($definition['default'] ?? '');
    }

    /** @return array<string, string> */
    public static function pages(): array
    {
        return ['home' => 'হোমপেজ', 'shared' => 'হেডার ও ফুটার', 'contact' => 'যোগাযোগ', 'shop' => 'শপ', 'product' => 'পণ্যের পেজ', 'track' => 'অর্ডার ট্র্যাক', 'cart' => 'কার্ট', 'checkout' => 'চেকআউট', 'success' => 'অর্ডার সফল', 'account' => 'কাস্টমার অ্যাকাউন্ট', 'login' => 'লগইন', 'about' => 'আমাদের সম্পর্কে', 'delivery' => 'ডেলিভারি', 'returns' => 'রিটার্ন ও ওয়ারেন্টি'];
    }

    public static function sectionLabel(string $section): string
    {
        return match ($section) {
            'page_banner' => 'সব পেজের ব্যানার ছবি',
            'story' => 'গল্প ও পরিচিতি',
            'values' => 'আমাদের বিশেষত্ব',
            'contact' => 'যোগাযোগের আহ্বান',
            'hero' => 'ব্যানারের গাইড লিংক ও পরিসংখ্যান',
            'guide' => 'চেয়ার বাছাই গাইড',
            'testimonials' => 'রিভিউ শিরোনাম',
            'services' => 'সার্ভিস সুবিধা',
            'assurances' => 'ডেলিভারি, রিটার্ন ও ওয়ারেন্টি',
            'header' => 'হেডার ও মেনু',
            'footer' => 'ফুটার',
            'mobile_navigation' => 'মোবাইল মেনু',
            'heading' => 'পেজের শিরোনাম ও পরিচিতি',
            'contact_details' => 'যোগাযোগের তথ্য',
            'message_form' => 'মেসেজ ফর্ম',
            'help_links' => 'সহায়ক লিংক',
            'tracking_form' => 'ট্র্যাকিং ফর্ম ও ফলাফল',
            'cart' => 'কার্টের লেখা',
            'summary' => 'অর্ডার সারাংশ',
            'empty_cart' => 'খালি কার্ট',
            'delivery' => 'ডেলিভারি ফর্ম',
            'payment' => 'পেমেন্টের লেখা',
            'navigation' => 'অ্যাকাউন্ট মেনু',
            'overview' => 'অ্যাকাউন্টের পরিচিতি',
            'profile' => 'প্রোফাইল',
            'orders' => 'অর্ডারের তালিকা',
            'order_details' => 'অর্ডারের বিস্তারিত ও ট্র্যাকিং',
            'wishlist' => 'উইশলিস্ট',
            'wallet' => 'ওয়ালেট ও পয়েন্ট',
            'inbox' => 'ইনবক্স',
            'addresses' => 'ঠিকানা',
            'support' => 'সাপোর্ট টিকিট',
            'referrals' => 'রেফারেল',
            'coupons' => 'কুপন',
            'track' => 'অর্ডার ট্র্যাক',
            'metadata' => 'সাইটের টাইটেল ও বিবরণ',
            'login_form' => 'লগইন ফর্ম',
            'content' => 'পেজের বিস্তারিত',
            default => str_starts_with($section, 'banner_') ? 'ব্যানার '.substr($section, 7) : (str_starts_with($section, 'review_') ? 'ক্রেতার রিভিউ '.substr($section, 7) : ucfirst($section)),
        };
    }
}
