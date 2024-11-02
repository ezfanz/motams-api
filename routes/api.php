<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Complaint\ComplaintController;
use App\Http\Controllers\Attendance\AttendanceRecordController;
use App\Http\Controllers\Attendance\AttendanceReviewController;
use App\Http\Controllers\Attendance\ReviewStatusController;
use App\Http\Controllers\Leave\OfficeLeaveRequestController;
use App\Http\Controllers\Leave\LeaveTypeController;

/**
 * Public Routes for Authentication
 */
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function () {

    /**
     * Authenticated Auth Routes
     */
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::post('/profile', [AuthController::class, 'profile'])->name('auth.profile');
    });

    /**
     * User Management
     */
    Route::apiResource('users', UserController::class);

    /**
     * Complaints
     */
    Route::apiResource('complaints', ComplaintController::class);

    /**
     * Attendance Records
     */
    Route::prefix('attendance-records')->group(function () {
        // Custom routes for specific statuses
        Route::get('/tidak-hadir', [AttendanceRecordController::class, 'listTidakHadir']);
        Route::get('/datang-lewat', [AttendanceRecordController::class, 'listDatangLewat']);
        Route::get('/balik-awal', [AttendanceRecordController::class, 'listBalikAwal']);

        // General resource routes
        Route::apiResource('/', AttendanceRecordController::class);
    });

    /**
     * Leave Requests
     */
    Route::prefix('office-leave-requests')->group(function () {
        Route::get('/monthly', [OfficeLeaveRequestController::class, 'getByMonth']);
        Route::apiResource('/', OfficeLeaveRequestController::class);
    });

    /**
     * Leave Types
     */
    Route::get('leave-types', [LeaveTypeController::class, 'index']);

    /**
     * Attendance Review
     */
    Route::prefix('attendance-reviews')->group(function () {
        Route::get('/', [AttendanceReviewController::class, 'index'])->name('attendance-reviews.index');
        Route::post('/batch-update', [AttendanceReviewController::class, 'batchUpdate'])->name('attendance-reviews.batch-update');
        Route::get('/monthly-status-summary', [AttendanceReviewController::class, 'getMonthlyStatusSummary'])->name('attendance-status-summary.index');
    });

    /**
     * Review Status Options
     */
    Route::get('review-status-options', [ReviewStatusController::class, 'index'])->name('review-status-options.index');
});
