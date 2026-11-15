<?php

use App\Http\Controllers\Admin\CrudController;
use App\Http\Controllers\Admin\EstimateController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ShopFloorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\Public\SiteController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/catalog', [SiteController::class, 'catalog'])->name('catalog.index');
Route::get('/catalog/{product:slug}', [SiteController::class, 'product'])->name('catalog.show');
Route::get('/about', fn () => app(SiteController::class)->page('about'))->name('site.about');
Route::get('/capabilities', fn () => app(SiteController::class)->page('capabilities'))->name('site.capabilities');
Route::get('/services', fn () => app(SiteController::class)->page('services'))->name('site.services');
Route::get('/work', [SiteController::class, 'portfolio'])->name('portfolio.index');
Route::get('/work/{caseStudy:slug}', [SiteController::class, 'study'])->name('portfolio.show');
Route::get('/journal', [SiteController::class, 'blog'])->name('blog.index');
Route::get('/journal/{post:slug}', [SiteController::class, 'post'])->name('blog.show');
Route::get('/contact', [SiteController::class, 'contact'])->name('site.contact');
Route::post('/contact', [SiteController::class, 'quote'])->name('site.quote');
Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'bn'], true), 404);
    session(['locale' => $locale]);

    return back();
})->name('locale');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::match(['get', 'post'], '/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('index');
});

Route::middleware(['auth', 'staff'])->prefix('admin')->group(function () {
    Route::get('/resources/{resource}', [CrudController::class, 'index'])->name('admin.resources.index');
    Route::get('/resources/{resource}/create', [CrudController::class, 'create'])->name('admin.resources.create');
    Route::post('/resources/{resource}', [CrudController::class, 'store'])->name('admin.resources.store');
    Route::get('/resources/{resource}/{id}', [CrudController::class, 'show'])->whereNumber('id')->name('admin.resources.show');
    Route::get('/resources/{resource}/{id}/edit', [CrudController::class, 'edit'])->whereNumber('id')->name('admin.resources.edit');
    Route::put('/resources/{resource}/{id}', [CrudController::class, 'update'])->whereNumber('id')->name('admin.resources.update');
    Route::delete('/resources/{resource}/{id}', [CrudController::class, 'destroy'])->whereNumber('id')->name('admin.resources.destroy');

    Route::post('/sales-orders/{id}/confirm', [CrudController::class, 'confirmOrder'])->whereNumber('id')->name('admin.orders.confirm');
    Route::post('/sales-orders/{id}/invoice', [CrudController::class, 'invoiceOrder'])->whereNumber('id')->name('admin.orders.invoice');
    Route::post('/issues/{id}/diagnose', [CrudController::class, 'diagnose'])->whereNumber('id')->name('admin.issues.diagnose');
    Route::get('/invoices/{id}/pdf', [CrudController::class, 'invoicePdf'])->whereNumber('id')->name('admin.invoices.pdf');
    Route::get('/jobs/{id}/pdf', [CrudController::class, 'jobCard'])->whereNumber('id')->name('admin.jobs.pdf');

    Route::get('/estimate', [EstimateController::class, 'create'])->name('admin.estimate');
    Route::post('/estimate', [EstimateController::class, 'store'])->name('admin.estimate.store');
    Route::get('/shop-floor', [ShopFloorController::class, 'index'])->name('admin.shop-floor.index');
    Route::get('/shop-floor/{job}', [ShopFloorController::class, 'show'])->name('admin.shop-floor.show');
    Route::post('/shop-floor/stages/{stage}', [ShopFloorController::class, 'complete'])->name('admin.shop-floor.complete');
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
    Route::get('/modules', [SystemController::class, 'modules'])->name('admin.modules');
    Route::post('/modules', [SystemController::class, 'updateModules'])->name('admin.modules.update');
    Route::get('/attendance', [SystemController::class, 'attendance'])->name('admin.attendance');
});
