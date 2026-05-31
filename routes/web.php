<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

/* ── Auth (guest only) ── */
Route::middleware('guest')->group(function () {
    Route::get( 'login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('login',    [AuthController::class, 'login'])->name('login.post');
    Route::get( 'register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.post');
});

/* ── Authenticated ── */
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    /* Dashboard */
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* Purchase Requests */
    Route::get( 'purchase-request/create', [PurchaseRequestController::class, 'create'])->name('purchase_requests.create');
    Route::post('purchase-request',         [PurchaseRequestController::class, 'store'])->name('purchase_requests.store');
    Route::get( 'pr-list',                  [PurchaseRequestController::class, 'index'])->name('pr.list');
    Route::get( 'purchase-request/{pr}',    [PurchaseRequestController::class, 'show'])->name('purchase_requests.show');

    /* Procurement History */
    Route::get('procurement-history', [HistoryController::class, 'index'])->name('history.index');

    /* RFQ */
    Route::get( 'rfq/create', [RfqController::class, 'create'])->name('rfqs.create');
    Route::post('rfq',         [RfqController::class, 'store'])->name('rfqs.store');

    /* Vendor Selection (new flow — main page) */
    Route::get( 'vendor-selection',       [VendorController::class, 'index'])->name('vendors.list');
    Route::post('vendor-selection/store', [VendorController::class, 'storeSelection'])->name('vendors.store.selection');

    /* Vendor Selection (old RFQ-based flow — backward compat, GET only) */
    Route::get('vendor/select/{rfq}', [VendorController::class, 'select'])->name('vendors.select');
    // POST route removed — old store() method no longer exists; use vendor-selection/store instead

    /* Quotation */
    Route::get( 'quotation/status/{rfq}', [QuotationController::class, 'status'])->name('quotations.status');
    Route::post('quotation/status/{rfq}', [QuotationController::class, 'updateStatus'])->name('quotations.updateStatus');
    Route::get( 'quotation/final/{rfq}',  [QuotationController::class, 'final'])->name('quotations.final');
    Route::post('quotation/final/{rfq}',  [QuotationController::class, 'storeFinal'])->name('quotations.storeFinal');
});