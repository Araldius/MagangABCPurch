<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register',[AuthController::class, 'register'])->name('register.post');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard (role-aware)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Purchase Requests
    Route::get( 'purchase-request/create',  [PurchaseRequestController::class, 'create'])->name('purchase_requests.create');
    Route::post('purchase-request',          [PurchaseRequestController::class, 'store'])->name('purchase_requests.store');
    Route::get( 'pr-list',                   [PurchaseRequestController::class, 'index'])->name('pr.list');
    Route::get( 'purchase-request/{pr}',     [PurchaseRequestController::class, 'show'])->name('purchase_requests.show');

    // Procurement History
    Route::get('procurement-history', [HistoryController::class, 'index'])->name('history.index');

    // RFQ (purchasing role)
    Route::get( 'rfq/create',  [RfqController::class, 'create'])->name('rfqs.create');
    Route::post('rfq',          [RfqController::class, 'store'])->name('rfqs.store');

    // Vendor
    Route::get( 'vendor',               [VendorController::class, 'index'])->name('vendors.list');
    Route::get( 'vendor/select/{rfq}',  [VendorController::class, 'select'])->name('vendors.select');
    Route::post('vendor/select/{rfq}',  [VendorController::class, 'store'])->name('vendors.store');

    // Quotation
    Route::get( 'quotation/status/{rfq}', [QuotationController::class, 'status'])->name('quotations.status');
    Route::post('quotation/status/{rfq}', [QuotationController::class, 'updateStatus'])->name('quotations.updateStatus');
    Route::get( 'quotation/final/{rfq}',  [QuotationController::class, 'final'])->name('quotations.final');
    Route::post('quotation/final/{rfq}',  [QuotationController::class, 'storeFinal'])->name('quotations.storeFinal');
});