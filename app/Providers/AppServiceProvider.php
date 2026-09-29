<?php

namespace App\Providers;

use App\Support\PageRepository;
use App\Support\SiteContent;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('admin-login', fn ($request) => Limit::perMinute(5)->by((string) ($request->ip() ?? 'unknown')));
        View::composer(['admin.*', 'layouts.site', 'site.*', 'partials.*'], function ($view) {
            if (! $view->offsetExists('site') || ! $view->offsetExists('menus')) {
                $content = app(SiteContent::class);
                if (! $view->offsetExists('site')) {
                    $view->with('site', $content->settings());
                }
                if (! $view->offsetExists('menus')) {
                    $view->with('menus', $content->menus());
                }
            }
        });

        View::composer('admin.*', function ($view) {
            if (! session('is_admin')) {
                return;
            }
            $pages = app(PageRepository::class);
            if (! $view->offsetExists('groups')) {
                $view->with('groups', $pages->grouped());
            }
            if (! $view->offsetExists('pendingApprovalCount')) {
                $view->with('pendingApprovalCount', $pages->draftCount());
            }
        });
    }
}
