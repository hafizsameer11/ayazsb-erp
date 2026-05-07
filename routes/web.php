<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Erp\AdminAccessController;
use App\Http\Controllers\Erp\AccountsFinanceController;
use App\Http\Controllers\Erp\DocumentationController;
use App\Http\Controllers\Erp\ModulePageController;
use App\Http\Controllers\Erp\ReportController;
use App\Support\PermissionRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return Auth::check()
        ? redirect()->route('erp.accounts.dashboard')
        : redirect()->route('login');
});

Route::view('/welcome', 'welcome')->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::prefix('erp/accounts')->name('erp.accounts.')->group(function () {
        Route::get('/', [AccountsFinanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/coa', [AccountsFinanceController::class, 'chartOfAccounts'])->name('coa');
        Route::post('/coa', [AccountsFinanceController::class, 'storeAccount'])->name('coa.store');
        Route::get('/opening', [AccountsFinanceController::class, 'accountsOpening'])->name('opening');
        Route::post('/opening', [AccountsFinanceController::class, 'storeOpening'])->name('opening.store');
        Route::get('/financial-year', [AccountsFinanceController::class, 'financialYear'])->name('financial-year');
        Route::post('/financial-year', [AccountsFinanceController::class, 'storeFinancialYear'])->name('financial-year.store');
        Route::get('/vouchers/jv', [AccountsFinanceController::class, 'journalVoucher'])->name('vouchers.jv');
        Route::get('/vouchers/cp', [AccountsFinanceController::class, 'cashPaymentVoucher'])->name('vouchers.cp');
        Route::get('/vouchers/cr', [AccountsFinanceController::class, 'cashReceiptVoucher'])->name('vouchers.cr');
        Route::get('/vouchers/bpv', [AccountsFinanceController::class, 'bankPaymentVoucher'])->name('vouchers.bpv');
        Route::get('/vouchers/brv', [AccountsFinanceController::class, 'bankReceiptVoucher'])->name('vouchers.brv');
        Route::get('/vouchers/cv', [AccountsFinanceController::class, 'cashVoucher'])->name('vouchers.cv');
        Route::post('/vouchers/{voucherType}', [AccountsFinanceController::class, 'storeVoucher'])->name('vouchers.store');
        Route::post('/vouchers/{voucher}/post', [AccountsFinanceController::class, 'postVoucher'])->name('vouchers.post');
        Route::get('/vouchers/{voucher}/print', [AccountsFinanceController::class, 'printVoucher'])->name('vouchers.print');
    });

    Route::prefix('erp')->name('erp.')->group(function () {
        Route::get('/docs', [DocumentationController::class, 'index'])->name('docs');

        Route::get('/yarn', [ModulePageController::class, 'dashboard'])->defaults('module', 'yarn')->name('yarn.dashboard');
        Route::get('/yarn/{screen}', [ModulePageController::class, 'screen'])->defaults('module', 'yarn')->name('yarn.screen');
        Route::post('/yarn/{screen}', [ModulePageController::class, 'storeScreenData'])->defaults('module', 'yarn')->name('yarn.screen.store');
        Route::post('/yarn/{screen}/{transaction}/post', [ModulePageController::class, 'postScreenData'])->defaults('module', 'yarn')->name('yarn.screen.post');
        Route::get('/yarn/{screen}/{transaction}/print', [ModulePageController::class, 'printScreenData'])->defaults('module', 'yarn')->name('yarn.screen.print');

        Route::get('/grey', [ModulePageController::class, 'dashboard'])->defaults('module', 'grey')->name('grey.dashboard');
        Route::get('/grey/{screen}', [ModulePageController::class, 'screen'])->defaults('module', 'grey')->name('grey.screen');
        Route::post('/grey/{screen}', [ModulePageController::class, 'storeScreenData'])->defaults('module', 'grey')->name('grey.screen.store');
        Route::post('/grey/{screen}/{transaction}/post', [ModulePageController::class, 'postScreenData'])->defaults('module', 'grey')->name('grey.screen.post');
        Route::get('/grey/{screen}/{transaction}/print', [ModulePageController::class, 'printScreenData'])->defaults('module', 'grey')->name('grey.screen.print');

        Route::get('/reports', [ModulePageController::class, 'dashboard'])->defaults('module', 'reports')->name('reports.dashboard');
        Route::get('/reports/{screen}', [ModulePageController::class, 'screen'])->defaults('module', 'reports')->name('reports.screen');
        Route::get('/reports/{screen}/view', [ReportController::class, 'view'])->name('reports.view');
        Route::get('/reports/{screen}/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/{screen}/print', [ReportController::class, 'print'])->name('reports.print');

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [AdminAccessController::class, 'dashboard'])->name('dashboard');
            Route::get('/users', [AdminAccessController::class, 'users'])->name('users');
            Route::post('/users/{user}/roles', [AdminAccessController::class, 'updateUserRoles'])->name('users.update-roles');
            Route::get('/roles', [AdminAccessController::class, 'roles'])->name('roles');
            Route::post('/roles', [AdminAccessController::class, 'storeRole'])->name('roles.store');
            Route::patch('/roles/{role}', [AdminAccessController::class, 'updateRole'])->name('roles.update');
            Route::delete('/roles/{role}', [AdminAccessController::class, 'deleteRole'])->name('roles.delete');
            Route::post('/roles/{role}/permissions', [AdminAccessController::class, 'updateRolePermissions'])->name('roles.permissions');
        });
    });

    foreach (PermissionRegistry::routePermissionMap() as $routeName => $permission) {
        Route::getRoutes()->getByName($routeName)?->middleware("permission:{$permission}");
    }
});
