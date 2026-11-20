<?php

namespace App\Providers;

use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Blade;
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
        // @canmenu($menuId) ... @endcanmenu — ซ่อน/แสดง block ตามสิทธิ์เมนู admin
        // ของ user ที่ login อยู่ (ดู App\Helpers\PermissionHelper)
        Blade::if('canmenu', function (int|string $menuId) {
            return PermissionHelper::canMenu($menuId);
        });
    }
}
