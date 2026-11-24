<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CareerCategoryController;
use App\Http\Controllers\Admin\CareerDocumentController;
use App\Http\Controllers\Admin\CareerJobController;
use App\Http\Controllers\Admin\EditorImageController;
use App\Http\Controllers\Admin\HomeAgencyController;
use App\Http\Controllers\Admin\HomeBlockController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\OrgChartController;
use App\Http\Controllers\Admin\OrgGroupController;
use App\Http\Controllers\Admin\OrgMemberController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProcurementLookupController;
use App\Http\Controllers\Admin\ProcurementRecordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\CareersController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\NewsController;
use App\Http\Controllers\Web\ProcurementController;
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

    // Website languages: only users who can see the settings menu (admin_menus id 5)
    Route::prefix('settings/language')->name('admin.settings.language.')->middleware('menu.permission:5')->where(['language' => '[a-z]{2,3}'])->group(function () {
        Route::get('/', [LanguageController::class, 'index'])->name('index');
        Route::get('/create', [LanguageController::class, 'create'])->name('create');
        Route::post('/', [LanguageController::class, 'store'])->name('store');
        Route::get('/{language}/edit', [LanguageController::class, 'edit'])->name('edit');
        Route::put('/{language}', [LanguageController::class, 'update'])->name('update');
        Route::get('/{language}/strings', [LanguageController::class, 'strings'])->name('strings');
        Route::put('/{language}/strings', [LanguageController::class, 'saveStrings'])->name('strings.save');
        Route::post('/{language}/strings/upload', [LanguageController::class, 'uploadStrings'])->name('strings.upload');
        Route::get('/{language}/download', [LanguageController::class, 'download'])->name('download');
    });
    // Recruitment notices (admin_menus id 14): positions with their documents, job families
    Route::prefix('careers')->name('admin.careers.')->middleware('menu.permission:14')->group(function () {
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [CareerJobController::class, 'index'])->name('index');
            Route::get('/create', [CareerJobController::class, 'create'])->name('create');
            Route::post('/', [CareerJobController::class, 'store'])->name('store');
            Route::get('/{job}/edit', [CareerJobController::class, 'edit'])->name('edit');
            Route::put('/{job}', [CareerJobController::class, 'update'])->name('update');
            Route::delete('/{job}', [CareerJobController::class, 'destroy'])->name('destroy');
            Route::post('/{job}/documents', [CareerDocumentController::class, 'store'])->name('documents.store');
        });
        Route::delete('/documents/{document}', [CareerDocumentController::class, 'destroy'])->name('documents.destroy');
        Route::post('/documents/{document}/move', [CareerDocumentController::class, 'move'])->name('documents.move');
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CareerCategoryController::class, 'index'])->name('index');
            Route::post('/', [CareerCategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [CareerCategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [CareerCategoryController::class, 'destroy'])->name('destroy');
        });
    });
    // Home page content (admin_menus id 2): hero video, about story, video presentation, related agencies
    Route::prefix('content')->name('admin.content.')->middleware('menu.permission:2')->group(function () {
        foreach (['hero', 'story', 'presentation'] as $section) {
            Route::prefix($section)->name("{$section}.")->group(function () use ($section) {
                Route::get('/', [HomeBlockController::class, 'edit'])->defaults('section', $section)->name('edit');
                Route::put('/', [HomeBlockController::class, 'update'])->defaults('section', $section)->name('update');
            });
        }
        Route::prefix('agencies')->name('agencies.')->group(function () {
            Route::get('/', [HomeAgencyController::class, 'index'])->name('index');
            Route::get('/create', [HomeAgencyController::class, 'create'])->name('create');
            Route::post('/', [HomeAgencyController::class, 'store'])->name('store');
            Route::get('/{agency}/edit', [HomeAgencyController::class, 'edit'])->name('edit');
            Route::put('/{agency}', [HomeAgencyController::class, 'update'])->name('update');
            Route::delete('/{agency}', [HomeAgencyController::class, 'destroy'])->name('destroy');
            Route::patch('/{agency}/toggle', [HomeAgencyController::class, 'toggle'])->name('toggle');
            Route::post('/{agency}/move', [HomeAgencyController::class, 'move'])->name('move');
        });
    });
    // Organization: board + committees, management team, structure chart (admin_menus id 15)
    Route::prefix('organization')->name('admin.organization.')->middleware('menu.permission:15')->group(function () {
        foreach (['board', 'management'] as $section) {
            Route::prefix($section)->name("{$section}.")->group(function () use ($section) {
                $r = fn (Illuminate\Routing\Route $route) => $route->defaults('section', $section);
                $r(Route::get('/', [OrgMemberController::class, 'index'])->name('index'));
                $r(Route::get('/members/create', [OrgMemberController::class, 'create'])->name('members.create'));
                $r(Route::post('/members', [OrgMemberController::class, 'store'])->name('members.store'));
                $r(Route::get('/members/{member}/edit', [OrgMemberController::class, 'edit'])->name('members.edit'));
                $r(Route::put('/members/{member}', [OrgMemberController::class, 'update'])->name('members.update'));
                $r(Route::delete('/members/{member}', [OrgMemberController::class, 'destroy'])->name('members.destroy'));
                $r(Route::get('/groups/create', [OrgGroupController::class, 'create'])->name('groups.create'));
                $r(Route::post('/groups', [OrgGroupController::class, 'store'])->name('groups.store'));
                $r(Route::get('/groups/{group}/edit', [OrgGroupController::class, 'edit'])->name('groups.edit'));
                $r(Route::put('/groups/{group}', [OrgGroupController::class, 'update'])->name('groups.update'));
                $r(Route::delete('/groups/{group}', [OrgGroupController::class, 'destroy'])->name('groups.destroy'));
            });
        }
        Route::get('/chart', [OrgChartController::class, 'index'])->name('chart.index');
        Route::post('/chart/{language}', [OrgChartController::class, 'store'])->where('language', '[a-z]{2,3}')->name('chart.store');
        Route::delete('/chart/{language}', [OrgChartController::class, 'destroy'])->where('language', '[a-z]{2,3}')->name('chart.destroy');
    });
    // Procurement (admin_menus id 7): five record types, master lists, vendors
    Route::prefix('procurement')->name('admin.procurement.')->middleware('menu.permission:7')->group(function () {
        foreach (['plan' => 'plan', 'price' => 'price', 'notice' => 'notice', 'winner-in' => 'winner_in', 'winner-out' => 'winner_out'] as $segment => $type) {
            Route::prefix($segment)->name("{$type}.")->group(function () use ($type) {
                $r = fn (Illuminate\Routing\Route $route) => $route->defaults('type', $type);
                $r(Route::get('/', [ProcurementRecordController::class, 'index'])->name('index'));
                $r(Route::get('/create', [ProcurementRecordController::class, 'create'])->name('create'));
                $r(Route::post('/', [ProcurementRecordController::class, 'store'])->name('store'));
                $r(Route::get('/{record}/edit', [ProcurementRecordController::class, 'edit'])->name('edit'));
                $r(Route::put('/{record}', [ProcurementRecordController::class, 'update'])->name('update'));
                $r(Route::delete('/{record}', [ProcurementRecordController::class, 'destroy'])->name('destroy'));
            });
        }

        // The three choice lists, one page each
        foreach (['agencies' => 'agency', 'work-types' => 'work_type', 'methods' => 'method'] as $segment => $kind) {
            Route::prefix($segment)->name("{$kind}.")->group(function () use ($kind) {
                $r = fn (Illuminate\Routing\Route $route) => $route->defaults('kind', $kind);
                $r(Route::get('/', [ProcurementLookupController::class, 'index'])->name('index'));
                $r(Route::post('/', [ProcurementLookupController::class, 'store'])->name('store'));
                $r(Route::put('/{id}', [ProcurementLookupController::class, 'update'])->whereNumber('id')->name('update'));
                $r(Route::delete('/{id}', [ProcurementLookupController::class, 'destroy'])->whereNumber('id')->name('destroy'));
            });
        }

    });
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
        Route::get('/about/board', [AboutController::class, 'board'])->name('web.about.board');
        Route::get('/about/management', [AboutController::class, 'management'])->name('web.about.management');
        Route::get('/about/structure', [AboutController::class, 'structure'])->name('web.about.structure');
        Route::get('/procurement', [ProcurementController::class, 'index'])->name('web.procurement');
        Route::get('/careers', [CareersController::class, 'index'])->name('web.careers');
        Route::get('/careers/{slug}', [CareersController::class, 'show'])->name('web.careers.show');
        Route::get('/news', [NewsController::class, 'index'])->name('web.news.index');
        Route::get('/news/videos', [NewsController::class, 'videos'])->name('web.news.videos');
        Route::get('/news/{slug}', [NewsController::class, 'show'])->name('web.news.show');
    });
