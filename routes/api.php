<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Complaint\ComplaintController;
use App\Http\Controllers\Attendance\AttendanceRecordController;
use App\Http\Controllers\Attendance\AttendanceReviewController;
use App\Http\Controllers\Attendance\AttendanceLogController;
use App\Http\Controllers\Attendance\ReviewStatusController;
use App\Http\Controllers\Attendance\VerificationController;
use App\Http\Controllers\Leave\OfficeLeaveRequestController;
use App\Http\Controllers\Leave\LeaveTypeController;
use App\Http\Controllers\Leave\OfficeLeaveApprovalController;
use App\Http\Controllers\Colour\ColourChangeController;


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
    Route::prefix('users')->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
        Route::get('/colour-changes', [ColourChangeController::class, 'getUserColourChanges']);

    });
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
        Route::get('/status/{idpeg}', [AttendanceRecordController::class, 'getAttendanceLogs'])->where('idpeg', '[0-9]+');


        // General resource routes
        Route::apiResource('/', AttendanceRecordController::class);
    });

    /**
     * Attendance Logs
     */
    Route::prefix('attendance-logs')->group(function () {
        Route::get('/', [AttendanceLogController::class, 'index']);
        Route::get('/{date}', [AttendanceLogController::class, 'show']);
        Route::post('/', [AttendanceLogController::class, 'store']);
    });

    /**
     * Office Leave Management
     */
    Route::prefix('office-leave')->group(function () {

        // Office Leave Requests
        Route::get('requests/monthly', [OfficeLeaveRequestController::class, 'getByMonth']);
        Route::get('requests/count-approval', [OfficeLeaveRequestController::class, 'countApproval']);

        Route::apiResource('requests', OfficeLeaveRequestController::class); // Register without additional prefix

        // Office Leave Approvals
        Route::prefix('approvals')->group(function () {
            Route::get('/', [OfficeLeaveApprovalController::class, 'index']);
            Route::get('/status-options', [OfficeLeaveApprovalController::class, 'getStatusOptions']);
            Route::post('/batch-update', [OfficeLeaveApprovalController::class, 'batchUpdate']);
            Route::get('/summary', [OfficeLeaveApprovalController::class, 'getMonthlySummary']);
        });
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

    /**
     * Attendance Verification
     */
    Route::prefix('office-verifications')->group(function () {
        Route::get('/', [VerificationController::class, 'index']);
        Route::get('/status-options', [VerificationController::class, 'getStatusOptions']);
        Route::post('/batch-update', [VerificationController::class, 'batchUpdate']);
        Route::get('/summary', [VerificationController::class, 'getMonthlySummary']);
    });
});
