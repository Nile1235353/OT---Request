<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\OtRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OtAttendanceController;

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
    // User အချက်အလက်ပြင်ရန်
    Route::put('/users/{user}', [RegisteredUserController::class, 'update'])->name('users.update');

    // Password သီးသန့်ပြင်ရန်
    Route::put('/users/{user}/password', [RegisteredUserController::class, 'updatePassword'])->name('users.updatePassword');
});

// Route::get('/users', [RegisteredUserController::class, 'create'])->name('users.create');
// Route::post('/users/create', [RegisteredUserController::class, 'store'])->name('users.store');

// My OT Routes
Route::middleware('auth')->group(function () {
    // Route::get('/my-ot', [OtRequestController::class, 'myotView'])->name('myot.view');
    Route::get('/my-ot', [OtRequestController::class, 'myotView'])->name('my-ot.dashboard');
    Route::post('/my-ot/{job}/acknowledge', [OtRequestController::class, 'acknowledge'])->name('my-ot.acknowledge');
    Route::get('/my-ot', [OtRequestController::class, 'myotView'])->name('my-ot.view');
});

// OT Request Routes
Route::middleware('auth')->group(function () {
    // Route::get('/request-ot', [OtRequestController::class, 'requestOtView'])->name('requestot.view');
    Route::get('/request-ot', [OtRequestController::class, 'create'])->name('overtime.create');
    Route::post('/overtime', [OtRequestController::class, 'store'])->name('overtime.store');
    // AJAX route to fetch employees by department
    Route::get('/get-employees-by-dept', [OtRequestController::class, 'getEmployeesByDept'])->name('employees.by.dept');
});

// Approve OT Routes
Route::middleware('auth')->group(function(){
    Route::get('/approve-ot', [OtRequestController::class, 'otApprove'])->name('ot.approve');
    Route::post('/approve-ot/{otRequest}/approve', [OtRequestController::class, 'approve'])->name('approvals.approve');
    Route::post('/approve-ot/{otRequest}/reject', [OtRequestController::class, 'reject'])->name('approvals.reject');
});

Route::get('/users/{user}/ot', [UserController::class, 'getOvertimeData'])
    ->name('users.ot_data')
    ->middleware(['auth', 'verified']);

// Overtime Reports Routes

Route::get('/reports/employee-ot', [OtRequestController::class, 'employeeOtReport'])->name('reports.employee-ot')->middleware(['auth', 'verified']);
Route::get('/reports/employee-ot/export', [OtRequestController::class, 'exportEmployeeOt'])->name('reports.employee_ot.export')->middleware(['auth', 'verified']);

// OT Page ကြည့်ရန် (GET)
Route::get('/ot-attendance', [OtAttendanceController::class, 'index'])->name('ot.attendance.index')->middleware(['auth', 'verified']);

// OT Attendance Update လုပ်ရန် (PUT)
Route::put('/ot-attendance/{id}', [OtAttendanceController::class, 'update'])->name('ot.attendance.update')->middleware(['auth', 'verified']);

// Excel Import လုပ်ရန် (POST)
Route::post('/ot-attendance/import', [OtAttendanceController::class, 'import'])->name('ot.attendance.import')->middleware(['auth', 'verified']);


// OT Report Task Update Route
Route::put('/assign-team/{id}/update-task', [OtRequestController::class, 'updateTask'])
    ->name('assign_team.update_task')
    ->middleware(['auth', 'verified']);


require __DIR__.'/auth.php';
