<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Head\DashboardController as HeadDashboardController;
use App\Http\Controllers\Branch\DashboardController as BranchDashboardController;
use App\Http\Controllers\Branch\CashflowController as BranchCashflowController;
use App\Http\Controllers\Admin\SetupController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Head\FileController;
use App\Http\Controllers\Head\CashflowController as HeadCashflowController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/setup', [SetupController::class, 'index'])->name('setup');
    Route::get('/users', [UsersController::class, 'index'])->name('users');
});

Route::prefix('head')->name('head.')->middleware(['auth', 'role:head'])->group(function () {
    Route::get('/dashboard', [HeadDashboardController::class, 'index'])->name('dashboard');
    Route::get('/file', [FileController::class, 'index'])->name('file');
    Route::get('/cashflow', [HeadCashflowController::class, 'index'])->name('cashflow');
});

Route::prefix('branch')->name('branch.')->middleware(['auth', 'role:branch'])->group(function () {
    Route::get('/dashboard', [BranchDashboardController::class, 'index'])->name('dashboard');
    Route::get('/cashflow', [BranchCashflowController::class, 'index'])->name('cashflow');
});
