<?php

use App\Http\Controllers\AdHocController;
use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\DailyPlanController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExposureController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WaSchedulerController;
use Illuminate\Support\Facades\Route;

Route::pattern('project', '[0-9]+');
Route::pattern('adhoc', '[0-9]+');
Route::pattern('dailyPlan', '[0-9]+');
Route::pattern('dailyTask', '[0-9]+');
Route::pattern('activity', '[0-9]+');
Route::pattern('schedule', '[0-9]+');
Route::pattern('user', '[0-9]+');
Route::pattern('skillset', '[0-9]+');

Route::middleware('kmi.guest')->group(function () {
    Route::get('/login', [AuthPageController::class, 'login'])->name('login');
    Route::post('/login', [AuthPageController::class, 'authenticate'])->name('login.authenticate');
    Route::get('/register', [AuthPageController::class, 'register'])->name('register');
    Route::post('/register', [AuthPageController::class, 'store'])->name('register.store');
});

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

Route::middleware('kmi.auth')->group(function () {
    Route::post('/logout', [AuthPageController::class, 'logout'])->name('logout');

    // 1. Projects
    Route::resource('projects', ProjectController::class);

    // 2. Exposure S-Curve
    // 2. Ad Hoc Initiatives
    Route::resource('adhocs', AdHocController::class);

    // 3. Exposure S-Curve
    Route::get('exposure', [ExposureController::class, 'index'])->name('exposure.index');

    // 3. Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        // Daily Task (Handsontable JS Spreadsheet)
        Route::get('daily-tasks', [DailyTaskController::class, 'index'])->name('daily-tasks');
        Route::post('daily-tasks/batch-save', [DailyTaskController::class, 'batchSave'])->name('daily-tasks.batch-save');
        Route::get('daily-tasks/export-excel', [DailyTaskController::class, 'exportExcel'])->name('daily-tasks.export-excel');
        Route::post('daily-tasks/{dailyTask}/attachment', [DailyTaskController::class, 'uploadAttachment'])->name('daily-tasks.attachment.upload');
        Route::get('daily-tasks/{dailyTask}/attachment', [DailyTaskController::class, 'viewAttachment'])->name('daily-tasks.attachment.view');
        Route::delete('daily-tasks/{dailyTask}/attachment', [DailyTaskController::class, 'deleteAttachment'])->name('daily-tasks.attachment.delete');

        // Daily Plan (Weekly Format & Mon-Fri Activity Planner)
        Route::get('daily-plans', [DailyPlanController::class, 'index'])->name('daily-plans');
        Route::post('daily-plans', [DailyPlanController::class, 'store'])->name('daily-plans.store');
        Route::get('daily-plans/{dailyPlan}', [DailyPlanController::class, 'show'])->name('daily-plans.show');
        Route::delete('daily-plans/{dailyPlan}', [DailyPlanController::class, 'destroy'])->name('daily-plans.destroy');
        Route::post('daily-plans/{dailyPlan}/activities', [DailyPlanController::class, 'storeActivity'])->name('daily-plans.activities.store');
        Route::delete('daily-plans/activities/{activity}', [DailyPlanController::class, 'destroyActivity'])->name('daily-plans.activities.destroy');

        // Monthly Report Dashboard
        Route::get('monthly-report', [MonthlyReportController::class, 'index'])->name('monthly-report');
    });

    // Convenience aliases
    Route::get('daily-plans/{dailyPlan}', [DailyPlanController::class, 'show'])->name('daily-plans.show');
    Route::post('daily-plans', [DailyPlanController::class, 'store'])->name('daily-plans.store');
    Route::delete('daily-plans/{dailyPlan}', [DailyPlanController::class, 'destroy'])->name('daily-plans.destroy');

    // 4. Master Data (Admin / Superadmin / Head)
    Route::prefix('master-data')->name('master.')->middleware('kmi.access:master-data')->group(function () {
        Route::get('/', [MasterDataController::class, 'index'])->name('index');
        Route::post('departments', [MasterDataController::class, 'storeDepartment'])->name('departments.store');
        Route::post('subdepartments', [MasterDataController::class, 'storeSubDepartment'])->name('subdepartments.store');
        Route::post('project-types', [MasterDataController::class, 'storeProjectType'])->name('project-types.store');
        Route::post('skillsets', [MasterDataController::class, 'storeSkillset'])->name('skillsets.store');
        Route::put('skillsets/{skillset}', [MasterDataController::class, 'updateSkillset'])->name('skillsets.update');
        Route::delete('skillsets/{skillset}', [MasterDataController::class, 'destroySkillset'])->name('skillsets.destroy');
        Route::post('users', [MasterDataController::class, 'storeUser'])->name('users.store');
        Route::put('users/{user}', [MasterDataController::class, 'updateUser'])->name('users.update');
    });

    // 5. WhatsApp Scheduler (Superadmin)
    Route::prefix('wa-scheduler')->name('wa-scheduler.')->middleware('kmi.access:wa-scheduler')->group(function () {
        Route::get('/', [WaSchedulerController::class, 'index'])->name('index');
        Route::post('/', [WaSchedulerController::class, 'store'])->name('store');
        Route::post('settings', [WaSchedulerController::class, 'updateSettings'])->name('settings.update');
        Route::post('{schedule}/trigger', [WaSchedulerController::class, 'trigger'])->name('trigger');
        Route::post('test-send', [WaSchedulerController::class, 'testSend'])->name('test-send');
        Route::delete('{schedule}', [WaSchedulerController::class, 'destroy'])->name('destroy');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
});
