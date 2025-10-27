<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\OtRequestController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User Management Routes

Route::middleware('auth')->group(function () {
    Route::get('/users', [RegisteredUserController::class, 'create'])->name('users.create');
    Route::post('/users/create', [RegisteredUserController::class, 'store'])->name('users.store');
});

// Route::get('/users', [RegisteredUserController::class, 'create'])->name('users.create');
// Route::post('/users/create', [RegisteredUserController::class, 'store'])->name('users.store');

// My OT Routes
Route::middleware('auth')->group(function () {
    // Route::get('/my-ot', [OtRequestController::class, 'myotView'])->name('myot.view');
    Route::get('/my-ot', [OtRequestController::class, 'myotView'])->name('my-ot.dashboard');
    Route::post('/my-ot/{job}/acknowledge', [OtRequestController::class, 'acknowledge'])->name('my-ot.acknowledge');
});

// OT Request Routes
Route::middleware('auth')->group(function () {
    // Route::get('/request-ot', [OtRequestController::class, 'requestOtView'])->name('requestot.view');
    Route::get('/request-ot', [OtRequestController::class, 'create'])->name('overtime.create');
    Route::post('/overtime', [OtRequestController::class, 'store'])->name('overtime.store');
});

// Approve OT Routes
Route::middleware('auth')->group(function(){
    Route::get('/approve-ot', [OtRequestController::class, 'otApprove'])->name('ot.approve');
    Route::post('/approve-ot/{otRequest}/approve', [OtRequestController::class, 'approve'])->name('approvals.approve');
    Route::post('/approve-ot/{otRequest}/reject', [OtRequestController::class, 'reject'])->name('approvals.reject');
});

Route::get('/users/{user}/ot', [UserController::class, 'getOvertimeData'])->middleware(['auth', 'verified']);

require __DIR__.'/auth.php';
