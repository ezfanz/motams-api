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
use App\Http\Controllers\Leave\OfficeLeaveStatusController;
use App\Http\Controllers\Leave\OfficeLeaveApprovalController;
use App\Http\Controllers\Colour\ColourChangeController;
use App\Http\Controllers\Attendance\AttendanceApprovalController;
use App\Http\Controllers\Attendance\AttendanceStatusController;
use App\Http\Controllers\Attendance\AttendanceConfirmationController;
use App\Http\Controllers\Pengumuman\PengumumanController;
use App\Http\Controllers\Attendance\AttendanceActionController;
use App\Http\Controllers\Alasan\AlasanController;




/**
 * Public Routes for Authentication
 */
Route::prefix('v1/auth')->middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});


Route::group(['prefix' => 'v1', 'middleware' => ['auth:api', 'throttle:global']], function () {
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
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::prefix('users')->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
        Route::get('/colour-changes', [ColourChangeController::class, 'getUserColourChanges']);
    });
    Route::apiResource('users', UserController::class);

    /**
     * Complaints
     */
    Route::post('/aduan', [ComplaintController::class, 'sendAduan']);
    Route::apiResource('complaints', ComplaintController::class);


    /**
     * Attendance Records
     */
    Route::prefix('attendance-records')->group(function () {
        // Custom routes for specific statuses
        // Route::get('/tidak-hadir', [AttendanceRecordController::class, 'listTidakHadir']);
        // Route::get('/datang-lewat', [AttendanceRecordController::class, 'listDatangLewat']);
        // Route::get('/balik-awal', [AttendanceRecordController::class, 'listBalikAwal']);
        Route::get('/list', [AttendanceRecordController::class, 'listAttendanceRecords']);
        Route::get('/status/{idpeg}', [AttendanceRecordController::class, 'getAttendanceLogs'])->where('idpeg', '[0-9]+');
        Route::get('/review-count', [AttendanceRecordController::class, 'getReviewCounts']);
        Route::get('/approval-count', [AttendanceRecordController::class, 'getApprovalCounts']);

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
        Route::post('/approve', [OfficeLeaveApprovalController::class, 'approve'])->name('office-leave.approve');
        Route::get('/status/list', [OfficeLeaveStatusController::class, 'getLeaveStatus'])->name('office-leave-status.list');


        Route::apiResource('requests', OfficeLeaveRequestController::class); // Register without additional prefix

        // Office Leave Approvals
        Route::prefix('approvals')->group(function () {
            Route::get('/', [OfficeLeaveApprovalController::class, 'index']);
            Route::get('/status-options', [OfficeLeaveApprovalController::class, 'getStatusOptions']);
            Route::post('/batch-update', [OfficeLeaveApprovalController::class, 'batchUpdate']);
            Route::get('/summary', [OfficeLeaveApprovalController::class, 'getMonthlySummary']);
            Route::get('/status', [OfficeLeaveApprovalController::class, 'getSupervisedApprovalStatus'])
                ->name('office-leave.approvals.status-supervised');

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
        Route::get('/individual', [AttendanceReviewController::class, 'individualReview'])->name('attendance-reviews.individual');
        Route::post('/batch-update', [AttendanceReviewController::class, 'batchUpdate'])->name('attendance-reviews.batch-update');
        Route::get('/monthly-status-summary', [AttendanceReviewController::class, 'getMonthlyStatusSummary'])->name('attendance-status-summary.index');
        Route::post('/process', [AttendanceReviewController::class, 'processReview'])->name('attendance-reviews.process');
        Route::get('/{id}', [AttendanceReviewController::class, 'getReviewDetails'])->name('attendance-reviews.details');
    });

    /**
     * Review Status Options
     */
    Route::get('review-status-options', [ReviewStatusController::class, 'index'])->name('review-status-options.index');

    /**
     * Attendance Verification
     */
    Route::prefix('attendance-verifications')->group(function () {
        Route::get('/', [VerificationController::class, 'index']);
        Route::get('/status-options', [VerificationController::class, 'getStatusOptions']);
        Route::post('/batch-update', [VerificationController::class, 'batchUpdate']);
        Route::get('/summary', [VerificationController::class, 'getMonthlySummary']);
        Route::post('/batch-approve', [VerificationController::class, 'batchApprove'])->name('attendance-verifications.batch-approve');
        Route::post('/batch-review', [VerificationController::class, 'batchReview'])->name('attendance-verifications.batch-review');

    });

    /**
     * Attendance Approval
     */
    Route::prefix('attendance-approval')->group(function () {
        Route::get('/list', [AttendanceApprovalController::class, 'getApprovalList']);
        Route::get('/confirmation/{id}', [AttendanceConfirmationController::class, 'getConfirmationDetails'])
            ->name('attendance-confirmation.details');
        Route::post('/confirmation/process', [AttendanceConfirmationController::class, 'processConfirmation'])
            ->name('attendance-confirmation.process');
        Route::post('/batch-process', [AttendanceReviewController::class, 'batchProcess'])->name('attendance-reviews.batch-process');

    });

    /**
     * Attendance Status
     */
    Route::prefix('attendance-status')->group(function () {
        Route::get('/list', [AttendanceStatusController::class, 'getStatusList']);
    });

    /**
     * Attendance Action
     */
    Route::prefix('attendance-action')->group(function () {
        Route::post('/balik-awal', [AttendanceActionController::class, 'handleEarlyDeparture']);
        Route::post('/datang-lambat', [AttendanceActionController::class, 'handleLateArrival']);
        Route::post('/tidak-hadir', [AttendanceActionController::class, 'handleAbsent']);


    });


    /**
     * Pengumuman
     */
    Route::get('/pengumuman', [PengumumanController::class, 'getPengumuman']);

    /**
     * Alasan
     */
    Route::get('/alasan', [AlasanController::class, 'index']);

});
