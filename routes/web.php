<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\EditorImageController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\NewsController;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'redirectToDefault'])->name('web.root');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.home');

    // Image upload used by the rich text editor in any admin form
    Route::post('/editor/image', EditorImageController::class)->middleware('throttle:60,1')->name('admin.editor.image');

    // News management: only users who can see the news menu (admin_menus id 8)
    Route::prefix('news')->name('admin.news.')->middleware('menu.permission:8')->group(function () {
        // Categories first so "category" is never read as a {post}
        Route::prefix('category')->name('category.')->group(function () {
            Route::get('/', [NewsCategoryController::class, 'index'])->name('index');
            Route::get('/create', [NewsCategoryController::class, 'create'])->name('create');
            Route::post('/', [NewsCategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit', [NewsCategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [NewsCategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [NewsCategoryController::class, 'destroy'])->name('destroy');
        });
        Route::get('/', [AdminNewsController::class, 'index'])->name('index');
        Route::get('/create', [AdminNewsController::class, 'create'])->name('create');
        Route::post('/', [AdminNewsController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [AdminNewsController::class, 'edit'])->name('edit');
        Route::put('/{post}', [AdminNewsController::class, 'update'])->name('update');
        Route::delete('/{post}', [AdminNewsController::class, 'destroy'])->name('destroy');
    });
    // Permission pages: only users who can see the permission menu (admin_menus id 10)
    Route::prefix('permission')->middleware('menu.permission:10')->group(function () {
        Route::get('/group', [PermissionController::class, 'adminGroup'])->name('admin.permission.group');
        Route::post('/group', [PermissionController::class, 'storeAdminGroup'])->name('admin.permission.group.store');
        Route::get('/group/{adminGroup}/edit', [PermissionController::class, 'editAdminGroup'])->name('admin.permission.group.edit');
        Route::put('/group/{adminGroup}', [PermissionController::class, 'updateAdminGroup'])->name('admin.permission.group.update');
        Route::delete('/group/{adminGroup}', [PermissionController::class, 'destroyAdminGroup'])->name('admin.permission.group.destroy');
        Route::put('/group/{adminGroup}/permission', [PermissionController::class, 'updateAdminGroupPermission'])->name('admin.permission.group.permission.update');
        Route::get('/user', [PermissionController::class, 'adminUser'])->name('admin.permission.user');
        Route::post('/user', [PermissionController::class, 'storeAdminUser'])->name('admin.permission.user.store');
        Route::get('/user/{user}/edit', [PermissionController::class, 'editAdminUser'])->name('admin.permission.user.edit');
        Route::put('/user/{user}', [PermissionController::class, 'updateAdminUser'])->name('admin.permission.user.update');
        Route::put('/user/{user}/cancel', [PermissionController::class, 'cancelAdminUserPermission'])->name('admin.permission.user.cancel');
    });
});

Route::get('/lang/{locale}', LocaleController::class)->whereIn('locale', SetAdminLocale::SUPPORTED)->middleware('throttle:30,1')->name('lang.switch');

// Front office: keep last so fixed paths (login, admin, lang) win. The locale must be an active language, checked in SetWebLocale.
Route::prefix('{locale}')
    ->where(['locale' => '[a-z]{2,3}'])
    ->middleware('web.locale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('web.home');
        Route::get('/news', [NewsController::class, 'index'])->name('web.news.index');
        Route::get('/news/{slug}', [NewsController::class, 'show'])->name('web.news.show');
    });
