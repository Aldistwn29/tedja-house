<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// route dashboard admin
// Route::middleware(['role:admin'])->group(function () {
//     Route::get('/login/admin', function() {
//         return view('admin.login');
//     });
// })->name('dashoboard.admin');
// 
Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/category/{category:slug}', [FrontController::class, 'category'])->name('front.category');

Route::get('/details/{house:slug}', [FrontController::class, 'details'])->name('front.details');
Route::get('/search', [FrontController::class, 'search'])->name('front.search');

Route::match(['get', 'post'], '/mortgage/interest/payment/midtrans/notification', [DashboardController::class, 'payment_midtrans_notification'])
    ->name('front.payment_midtrans_notification');
// Route::match(['get', 'post'], '/mortgage/interest/payment/xendit/notification', [DashboardController::class, 'payment_midtrans_notification'])->name('front.payment_xendit_notification');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route untuk role customer
Route::middleware(['role:customer'])->group(function() {
    Route::get('/dashboard/mortgageRequest/{mortgageRequest}/installment/payment', [DashboardController::class, 'installment_payment'])->name('dashboard.installment.payment');
    Route::post('/dashboard/mortgageRequest/installment/payment', [DashboardController::class, 'payment_store_midtrans'])->name('dashboard.installment.payment_status_store_midtrans');
    // Route::post('/dashboard/mortgageRequest/installment/payment', [DashboardController::class, 'payment_store_xendit'])->name('dashboard.installment.payment_status_store_xendit');
    // Route::post('payment/xendit/callback', [DashboardController::class, 'payment_xendit_notification'])->name('dashboard.installment.payment_xendit_notification');
    Route::get('/request/mortgage/{interest}', [FrontController::class, 'interest'])->name('front.interest');
    Route::post('/request/mortgage/submited', [FrontController::class, 'request_interest'])->name('front.interest.submitted');
    Route::get('/request/success', [FrontController::class, 'request_success'])->name('front.request_success');
    // dashboard Route for mortgeg request
    Route::get('/dashboard/mortgages/', [DashboardController::class, 'index'])->name('dashboard.mortgages');
    Route::get('/dashboard/mortgage/{mortgageRequest}', [DashboardController::class, 'details'])->name('dashboard.mortgage.detilas');
    Route::get('/dashboard/mortgage/installment/{installment}', [DashboardController::class, 'installment_details'])->name('dashboard.installment.details');
});

require __DIR__.'/auth.php';
