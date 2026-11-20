<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.home');

    // Permission pages: only users who can see the permission menu (admin_menus id 10)
    Route::prefix('permission')->middleware('menu.permission:10')->group(function () {
        Route::get('/group', [PermissionController::class, 'adminGroup'])->name('admin.permission.group');
        Route::post('/group', [PermissionController::class, 'storeAdminGroup'])->name('admin.permission.group.store');
        Route::get('/group/{adminGroup}/edit', [PermissionController::class, 'editAdminGroup'])->name('admin.permission.group.edit');
        Route::put('/group/{adminGroup}', [PermissionController::class, 'updateAdminGroup'])->name('admin.permission.group.update');
        Route::delete('/group/{adminGroup}', [PermissionController::class, 'destroyAdminGroup'])->name('admin.permission.group.destroy');
        Route::put('/group/{adminGroup}/permission', [PermissionController::class, 'updateAdminGroupPermission'])->name('admin.permission.group.permission.update');
        Route::get('/user', [PermissionController::class, 'adminUser'])->name('admin.permission.user');
        Route::put('/user/{user}/cancel', [PermissionController::class, 'cancelAdminUserPermission'])->name('admin.permission.user.cancel');
    });
});

Route::get('/lang/{locale}', LocaleController::class)->whereIn('locale', SetAdminLocale::SUPPORTED)->middleware('throttle:30,1')->name('lang.switch');
