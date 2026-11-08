<?php

use App\Models\AdminMenu;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', function () {
    $menuConfig = collect(config('admin_menu'))->keyBy('id');

    $adminMenus = AdminMenu::active()->orderBy('sort_order')->get()->map(function ($menu) use ($menuConfig) {
        $meta = $menuConfig->get($menu->id);

        $menu->icon = $meta['icon'] ?? 'bi-dot';
        $menu->route_name = $meta['route'] ?? null;
        $menu->children = $meta['children'] ?? [];

        return $menu;
    });

    return view('admin.dashboard', compact('adminMenus'));
})->name('admin.dashboard');