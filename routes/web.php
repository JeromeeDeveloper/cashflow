<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Head\DashboardController as HeadDashboardController;
use App\Http\Controllers\Branch\DashboardController as BranchDashboardController;
use App\Http\Controllers\Branch\CashflowController as BranchCashflowController;

use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Head\FileController;
use App\Http\Controllers\Head\CashflowController as HeadCashflowController;
use App\Http\Controllers\Head\GLAccountController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UsersController::class, 'index'])->name('users');

    // User Management CRUD routes
    Route::get('/users/list', [UsersController::class, 'getUsers'])->name('users.list');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/stats', [UsersController::class, 'getStats'])->name('users.stats');
    Route::get('/users/export', [UsersController::class, 'export'])->name('users.export');
});

Route::prefix('head')->name('head.')->middleware(['auth', 'role:head'])->group(function () {
    Route::get('/dashboard', [HeadDashboardController::class, 'index'])->name('dashboard');
    Route::get('/file', [FileController::class, 'index'])->name('file');
    Route::get('/cashflow', [HeadCashflowController::class, 'index'])->name('cashflow');
    Route::get('/gl-accounts', [GLAccountController::class, 'index'])->name('gl-accounts');

    // GL Accounts CRUD routes
    Route::post('/gl-accounts', [GLAccountController::class, 'store'])->name('gl-accounts.store');
    Route::get('/gl-accounts/{glAccount}', [GLAccountController::class, 'show'])->name('gl-accounts.show');
    Route::put('/gl-accounts/{glAccount}', [GLAccountController::class, 'update'])->name('gl-accounts.update');
    Route::delete('/gl-accounts/{glAccount}', [GLAccountController::class, 'destroy'])->name('gl-accounts.destroy');
    Route::post('/gl-accounts/import', [GLAccountController::class, 'import'])->name('gl-accounts.import');
    Route::get('/gl-accounts/list', [GLAccountController::class, 'getAccounts'])->name('gl-accounts.list');

    // Cashflow CRUD routes
    Route::get('/cashflows', [HeadCashflowController::class, 'getCashflows'])->name('cashflows.index');
    Route::post('/cashflows', [HeadCashflowController::class, 'store'])->name('cashflows.store');
    Route::get('/cashflows/{cashflow}', [HeadCashflowController::class, 'show'])->name('cashflows.show');
    Route::put('/cashflows/{cashflow}', [HeadCashflowController::class, 'update'])->name('cashflows.update');
    Route::delete('/cashflows/{cashflow}', [HeadCashflowController::class, 'destroy'])->name('cashflows.destroy');
    Route::get('/cashflows/summary', [HeadCashflowController::class, 'getSummary'])->name('cashflows.summary');
    Route::get('/cashflows/export', [HeadCashflowController::class, 'export'])->name('cashflows.export');

    // File upload CRUD routes
    Route::get('/files', [FileController::class, 'getFiles'])->name('files.index');
    Route::post('/files', [FileController::class, 'store'])->name('files.store');
    Route::get('/files/{cashflowFile}', [FileController::class, 'show'])->name('files.show');
    Route::put('/files/{cashflowFile}', [FileController::class, 'update'])->name('files.update');
    Route::delete('/files/{cashflowFile}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::get('/files/{cashflowFile}/download', [FileController::class, 'download'])->name('files.download');
    Route::post('/files/{cashflowFile}/process', [FileController::class, 'process'])->name('files.process');
    Route::get('/files/stats', [FileController::class, 'getStats'])->name('files.stats');
});

Route::prefix('branch')->name('branch.')->middleware(['auth', 'role:branch'])->group(function () {
    Route::get('/dashboard', [BranchDashboardController::class, 'index'])->name('dashboard');
    Route::get('/cashflow', [BranchCashflowController::class, 'index'])->name('cashflow');

    // Branch Cashflow routes (read-only)
    Route::get('/cashflows', [BranchCashflowController::class, 'getCashflows'])->name('cashflows.index');
    Route::get('/cashflows/{cashflow}', [BranchCashflowController::class, 'show'])->name('cashflows.show');
    Route::get('/cashflows/summary', [BranchCashflowController::class, 'getSummary'])->name('cashflows.summary');
    Route::get('/cashflows/export', [BranchCashflowController::class, 'export'])->name('cashflows.export');
});
