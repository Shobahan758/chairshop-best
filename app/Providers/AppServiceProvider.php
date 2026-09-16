<?php

namespace App\Providers;

use App\Models\GeneralSetting;
use App\Models\TrackingSetting;
use App\Services\SiteContent;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        View::composer(['home', 'layouts.app', 'contact', 'track', 'cart', 'checkout', 'success', 'dashboard', 'auth.login', 'shop', 'product', 'order-invoice', 'page'], function ($view): void {
            $settings = Schema::hasTable('general_settings') ? GeneralSetting::first() : null;
            $view->with('siteContent', new SiteContent($settings?->site_content ?? [], $settings));
        });

        View::composer('layouts.app', function ($view): void {
            $view->with('trackingSettings', Schema::hasTable('tracking_settings') ? TrackingSetting::first() : null);
            $view->with('footerSettings', Schema::hasTable('general_settings') ? GeneralSetting::first() : null);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        RateLimiter::for('checkout', function (Request $request): Limit {
            return Limit::perMinute(10)
                ->by($request->user() ? 'user:'.$request->user()->id : 'ip:'.$request->ip())
                ->response(function (Request $request, array $headers): Response {
                    $message = 'এক মিনিট অপেক্ষা করে আবার অর্ডার দিন। আপনার তথ্য সংরক্ষিত আছে।';

                    if ($request->expectsJson()) {
                        return response()->json(['message' => $message], 429, $headers);
                    }

                    return redirect()->route('checkout')->withInput($request->except('_token'))
                        ->withErrors(['checkout' => $message])->withHeaders($headers);
                });
        });

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(10)->by(
                Str::transliterate(Str::lower($request->string('email')->toString())).'|'.$request->ip()
            );
        });
    }
}
