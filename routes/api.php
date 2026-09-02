<?php

use App\Models\MProject;
use App\Models\MWeeklyPlan;
use App\Models\TrDailyTask;
use App\Support\ExposureCurveBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('health', function () {
        return response()->json([
            'status' => 'OK',
            'app' => 'KMI Activity Plan API',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::get('projects', function () {
        return response()->json(
            MProject::with(['department', 'subDepartment', 'projectType', 'user', 'subProjects', 'directStages'])
                ->active()
                ->get()
        );
    });

    Route::get('exposure', function () {
        return response()->json(ExposureCurveBuilder::build());
    });

    Route::get('daily-tasks', function (Request $request) {
        $query = TrDailyTask::with(['user', 'project', 'subProject'])->orderBy('dtmTaskDate', 'desc');
        if ($request->filled('employee_id')) {
            $query->where('intUser_ID', $request->employee_id);
        }
        return response()->json($query->limit(100)->get());
    });

    Route::get('weekly-plans', function (Request $request) {
        $query = MWeeklyPlan::with(['user', 'activities'])->orderBy('dtmWeekStartDate', 'desc');
        if ($request->filled('employee_id')) {
            $query->where('intUser_ID', $request->employee_id);
        }
        return response()->json($query->limit(20)->get());
    });
});
