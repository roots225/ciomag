<?php

namespace App\Providers;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Services\MenuResolverService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MenuResolverService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.ciomag');

        View::composer('layouts.magazine', function ($view) {
            try {
                $menuResolver = app(MenuResolverService::class);
                $headerMenu = $menuResolver->resolveTree('header_main');
                $footerMenu1 = $menuResolver->resolveTree('footer_col_1');
                $footerMenu2 = $menuResolver->resolveTree('footer_col_2');

                $siteGeneral = Setting::getValue('site_general', [
                    'site_name' => 'CIOMAG',
                    'tagline' => 'Le Magazine des Dirigeants IT & de la Transformation Digitale',
                    'edition' => 'Édition Panafricaine & Internationale',
                    'logo_url' => '/images/ciomag-logo.png',
                    'favicon_url' => '/images/ciomag-logo.png',
                ]);

                $topCategories = Category::query()
                    ->whereNull('parent_id')
                    ->orderBy('display_order')
                    ->limit(8)
                    ->get();

                $breakingNews = Post::query()
                    ->where('status', PostStatus::Published)
                    ->orderByDesc('published_at')
                    ->limit(5)
                    ->get();
            } catch (\Throwable) {
                $headerMenu = [];
                $footerMenu1 = [];
                $footerMenu2 = [];
                $siteGeneral = [
                    'site_name' => 'CIOMAG',
                    'tagline' => 'Le Magazine des Dirigeants IT & de la Transformation Digitale',
                    'edition' => 'Édition Panafricaine & Internationale',
                    'logo_url' => '/images/ciomag-logo.png',
                    'favicon_url' => '/images/ciomag-logo.png',
                ];
                $topCategories = collect();
                $breakingNews = collect();
            }

            $view->with([
                'headerMenu' => $headerMenu,
                'footerMenu1' => $footerMenu1,
                'footerMenu2' => $footerMenu2,
                'siteGeneral' => $siteGeneral,
                'topCategories' => $topCategories,
                'breakingNews' => $breakingNews,
            ]);
        });
    }
}
