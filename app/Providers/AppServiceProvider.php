<?php

namespace App\Providers;

use App\Helpers\PermissionHelper;
use App\Models\Language;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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
        Paginator::useBootstrapFive();

        // @canmenu($menuId) ... @endcanmenu — ซ่อน/แสดง block ตามสิทธิ์เมนู admin
        // ของ user ที่ login อยู่ (ดู App\Helpers\PermissionHelper)
        Blade::if('canmenu', function (int|string $menuId) {
            return PermissionHelper::canMenu($menuId);
        });

        // Front office language dropdown: every web.* view gets the active
        // languages and the current one, without each controller passing them in.
        View::composer('web.layouts.main', function ($view) {
            $webLanguages = Language::active()->orderBy('sort_order')->get();

            $view->with([
                'webLanguages' => $webLanguages,
                'currentLanguage' => $webLanguages->firstWhere('code', app()->getLocale()),
            ]);
        });
    }
}
