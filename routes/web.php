<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\SubscriptionAdminController;
use App\Http\Controllers\Admin\AffiliateAdminController;
use App\Http\Controllers\Admin\AffiliateProRequestAdminController;
use App\Http\Controllers\Admin\ImageController;

$defineAdminRoutes = function () {

    Route::get('/', function () {
        return auth('admin')->check()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('admin.login.form');
    })->name('home');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('admin.auth')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/data', [ClientController::class, 'data'])->name('clients.data');
        Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.show');

        Route::get('/subscriptions', [SubscriptionAdminController::class, 'subscriptions'])->name('subscriptions.index');
        Route::get('/subscriptions/{id}', [SubscriptionAdminController::class, 'subscriptionShow'])->name('subscriptions.show');

        Route::get('/packages', [SubscriptionAdminController::class, 'packages'])->name('packages.index');
        Route::get('/packages/{id}', [SubscriptionAdminController::class, 'packageShow'])->name('packages.show');

        Route::get('/affiliate/codes', [AffiliateAdminController::class, 'codes'])->name('affiliate.codes');
        Route::get('/affiliate/codes/{id}', [AffiliateAdminController::class, 'codeShow'])->name('affiliate.codes.show');

        Route::get('/affiliate/commissions', [AffiliateAdminController::class, 'commissions'])->name('affiliate.commissions');

        // Bulk routes MUST be registered before {id} routes, otherwise "bulk" is captured as {id}.
        Route::post('/affiliate/commissions/bulk/status', [AffiliateAdminController::class, 'commissionBulkUpdateStatus'])->name('affiliate.commissions.bulk.status');
        Route::get('/affiliate/commissions/bulk/status', fn () => redirect()->route('admin.affiliate.commissions'));

        Route::get('/affiliate/commissions/{id}', [AffiliateAdminController::class, 'commissionShow'])
            ->whereNumber('id')
            ->name('affiliate.commissions.show');
        Route::post('/affiliate/commissions/{id}/status', [AffiliateAdminController::class, 'commissionUpdateStatus'])
            ->whereNumber('id')
            ->name('affiliate.commissions.status');

        Route::get('/affiliate/pro-requests', [AffiliateProRequestAdminController::class, 'index'])->name('affiliate.pro_requests.index');
        Route::get('/affiliate/pro-requests/{id}', [AffiliateProRequestAdminController::class, 'show'])->name('affiliate.pro_requests.show');
        Route::post('/affiliate/pro-requests/{id}/approve', [AffiliateProRequestAdminController::class, 'approve'])->name('affiliate.pro_requests.approve');
        Route::post('/affiliate/pro-requests/{id}/reject', [AffiliateProRequestAdminController::class, 'reject'])->name('affiliate.pro_requests.reject');
        Route::post('/affiliate/pro-requests/{id}/review', [AffiliateProRequestAdminController::class, 'review'])->name('affiliate.pro_requests.review');
        Route::post('/affiliate/pro-requests/external/{id}/review', [AffiliateProRequestAdminController::class, 'reviewExternal'])->name('affiliate.pro_requests.external.review');




Route::get('/images', [ImageController::class, 'index'])->name('images.index');
Route::get('/images/folder/{folder}', [ImageController::class, 'folder'])->name('images.folder');
    });
};

if (app()->environment('local')) {
    Route::name('admin.')->group($defineAdminRoutes);
} else {
    Route::domain('administrator.jodohmurni.com')
        ->name('admin.')
        ->group($defineAdminRoutes);
}
