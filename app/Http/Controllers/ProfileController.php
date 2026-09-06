<?php

namespace App\Http\Controllers;

use App\Models\MProject;
use App\Models\MUser;
use App\Models\MWeeklyPlan;
use App\Models\TrDailyPlanActivity;
use App\Models\TrDailyTask;
use App\Support\ExposureCurveBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $authUserId = session('auth_user_id');
        $targetUserId = $request->query('user_id', $authUserId);

        $user = MUser::with([
            'department',
            'subDepartment',
            'supervisedSubDepartments',
        ])->findOrFail($targetUserId);

        $isOwnProfile = ($user->intUser_ID === $authUserId);

        // 1. Projects statistics & list
        $projects = MProject::with([
            'projectType',
            'subDepartment',
            'assignments.user',
            'subProjects.stages',
            'subProjects.assignments.user',
            'directStages',
        ])
            ->forUser($user->intUser_ID)
            ->where('bitActive', true)
            ->orderBy('txtProjectName')
            ->get();

        $totalProjects = $projects->count();
        $totalWeight = (float) $projects->sum('floatWeight');
        $avgActual = $totalProjects > 0 ? round($projects->avg('floatActual'), 1) : 0;
        $avgPlan = $totalProjects > 0 ? round($projects->avg('floatPlan'), 1) : 0;
        $avgScore = $totalProjects > 0 ? round($projects->avg('intScore'), 1) : 0;

        // 2. Exposure Payload for S-Curve chart
        $exposurePayload = ExposureCurveBuilder::payload($projects);

        // 3. Daily Tasks statistics & recent tasks
        $dailyTasksQuery = TrDailyTask::with(['project.projectType', 'projectType', 'subProject', 'stage'])
            ->where('intUser_ID', $user->intUser_ID);

        $totalDailyTasks = (clone $dailyTasksQuery)->count();
        $totalHoursLogged = (float) (clone $dailyTasksQuery)->sum('floatDurationHours');
        $completedDailyTasks = (clone $dailyTasksQuery)->where('txtTaskStatus', 'Completed')->count();
        $inProgressDailyTasks = (clone $dailyTasksQuery)->where('txtTaskStatus', 'In Progress')->count();
        $recentDailyTasks = (clone $dailyTasksQuery)
            ->orderByDesc('dtmTaskDate')
            ->orderByDesc('intDailyTask_ID')
            ->take(8)
            ->get();

        // 4. Weekly Plans & Activities
        $weeklyPlansQuery = MWeeklyPlan::with(['activities'])
            ->where('intUser_ID', $user->intUser_ID)
            ->where('bitActive', true);

        $totalWeeklyPlans = (clone $weeklyPlansQuery)->count();
        $recentWeeklyPlans = (clone $weeklyPlansQuery)
            ->orderByDesc('dtmWeekStartDate')
            ->take(4)
            ->get();

        $totalActivities = TrDailyPlanActivity::where('intUser_ID', $user->intUser_ID)->count();
        $completedActivities = TrDailyPlanActivity::where('intUser_ID', $user->intUser_ID)->where('bitIsCompleted', true)->count();
        $activityCompletionRate = $totalActivities > 0 ? round(($completedActivities / $totalActivities) * 100, 1) : 0;

        // 5. Supervisor stats if role is Supervisor
        $supervisedStats = null;
        if ($user->isSupervisor()) {
            $supervisedSubDeptIds = $user->supervisedSubDepartments->pluck('intSubDepartment_ID');
            $supervisedStats = [
                'totalSupervisedProjects' => MProject::whereIn('intSubDepartment_ID', $supervisedSubDeptIds)->active()->count(),
                'totalSupervisedEmployees' => MUser::whereIn('intSubDepartment_ID', $supervisedSubDeptIds)->where('txtRole', 'Employee')->active()->count(),
            ];
        }

        return view('profile.show', compact(
            'user',
            'isOwnProfile',
            'projects',
            'totalProjects',
            'totalWeight',
            'avgActual',
            'avgPlan',
            'avgScore',
            'exposurePayload',
            'totalDailyTasks',
            'totalHoursLogged',
            'completedDailyTasks',
            'inProgressDailyTasks',
            'recentDailyTasks',
            'totalWeeklyPlans',
            'recentWeeklyPlans',
            'totalActivities',
            'completedActivities',
            'activityCompletionRate',
            'supervisedStats'
        ));
    }

    public function edit(): View
    {
        $user = MUser::with(['department', 'subDepartment'])->findOrFail(session('auth_user_id'));

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = MUser::findOrFail(session('auth_user_id'));

        $validated = $request->validate([
            'txtEmployeeName' => ['required', 'string', 'max:255'],
            'txtEmail' => ['required', 'email', 'max:255', Rule::unique('mUser', 'txtEmail')->ignore($user->intUser_ID, 'intUser_ID')],
            'txtPhone' => ['required', 'string', 'max:30'],
            'txtPosition' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $updateData = [
            'txtEmployeeName' => $validated['txtEmployeeName'],
            'txtEmail' => $validated['txtEmail'],
            'txtPhone' => $validated['txtPhone'],
            'txtPosition' => $validated['txtPosition'] ?? $user->txtPosition,
            'txtUpdatedBy' => $user->txtEmail,
            'dtmUpdated' => now(),
        ];

        if (! empty($validated['password'])) {
            $updateData['txtPassword'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $user = MUser::findOrFail(session('auth_user_id'));

        $request->validate([
            'profile_photo' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('profile_photo')->store('profile-photos', 'public');
        $user->update([
            'txtProfilePhoto' => $path,
            'txtUpdatedBy' => $user->txtEmail,
            'dtmUpdated' => now(),
        ]);

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }
}
