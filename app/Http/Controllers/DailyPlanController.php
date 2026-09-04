<?php

namespace App\Http\Controllers;

use App\Models\MProject;
use App\Models\MUser;
use App\Models\MWeeklyPlan;
use App\Models\TrDailyPlanActivity;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyPlanController extends Controller
{
    public function index(Request $request): View
    {
        $authUserId = session('auth_user_id');
        $authUser = MUser::find($authUserId);

        $query = MWeeklyPlan::with(['user', 'activities'])->orderBy('dtmWeekStartDate', 'desc');

        if ($authUser && ! $authUser->isSuperadmin()) {
            $query->where('intUser_ID', $authUser->intUser_ID);
        } elseif ($request->filled('employee')) {
            $query->where('intUser_ID', $request->employee);
        }

        $weeklyPlans = $query->get();

        if ($authUser && ! $authUser->isSuperadmin()) {
            $employees = MUser::active()->where('intUser_ID', $authUser->intUser_ID)->get();
        } else {
            $employees = MUser::active()->where('txtRole', '!=', 'Superadmin')->orderBy('txtEmployeeName')->get();
        }

        return view('reports.daily-plans', [
            'weeklyPlans' => $weeklyPlans,
            'employees' => $employees,
            'authUser' => $authUser,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $authUserId = session('auth_user_id') ?: 1;
        $authUser = MUser::find($authUserId);

        $validated = $request->validate([
            'txtWeekTitle' => ['required', 'string', 'max:200'],
            'dtmWeekStartDate' => ['required', 'date'],
            'dtmWeekEndDate' => ['required', 'date', 'after_or_equal:dtmWeekStartDate'],
            'intUser_ID' => ['nullable', 'integer'],
            'txtTargetGoals' => ['nullable', 'string'],
        ]);

        $userId = ! empty($validated['intUser_ID']) ? (int) $validated['intUser_ID'] : $authUserId;
        if ($authUser && ! $authUser->isSuperadmin()) {
            $userId = $authUser->intUser_ID;
        }

        $plan = MWeeklyPlan::create([
            'intUser_ID' => $userId,
            'txtWeekTitle' => $validated['txtWeekTitle'],
            'dtmWeekStartDate' => $validated['dtmWeekStartDate'],
            'dtmWeekEndDate' => $validated['dtmWeekEndDate'],
            'txtTargetGoals' => $validated['txtTargetGoals'] ?? null,
            'txtStatus' => 'Draft',
            'txtInsertedBy' => $authUser?->txtEmployeeName ?? 'System',
        ]);

        return redirect()->route('daily-plans.show', $plan)->with('success', 'Weekly Plan Card berhasil dibuat.');
    }

    public function show(MWeeklyPlan $dailyPlan): View
    {
        $authUser = MUser::find(session('auth_user_id'));
        if ($authUser && ! $authUser->isSuperadmin() && $dailyPlan->intUser_ID !== $authUser->intUser_ID) {
            abort(403, 'Unauthorized access to this weekly plan.');
        }

        $dailyPlan->load(['user.subDepartment', 'activities.project']);

        // Group activities by Monday - Friday
        $activitiesByDay = [
            'Senin' => collect(),
            'Selasa' => collect(),
            'Rabu' => collect(),
            'Kamis' => collect(),
            'Jumat' => collect(),
        ];

        foreach ($dailyPlan->activities as $act) {
            $day = $act->txtDayName;
            if (isset($activitiesByDay[$day])) {
                $activitiesByDay[$day]->push($act);
            }
        }

        // Calculate specific dates for Mon - Fri based on start date
        $startDate = $dailyPlan->dtmWeekStartDate ? Carbon::parse($dailyPlan->dtmWeekStartDate) : Carbon::now()->startOfWeek();
        $days = [
            'Senin' => $startDate->copy(),
            'Selasa' => $startDate->copy()->addDays(1),
            'Rabu' => $startDate->copy()->addDays(2),
            'Kamis' => $startDate->copy()->addDays(3),
            'Jumat' => $startDate->copy()->addDays(4),
        ];

        $targetUserId = $dailyPlan->intUser_ID ?: session('auth_user_id');
        $projects = MProject::active()
            ->when($targetUserId, fn($q) => $q->forUser($targetUserId))
            ->orderBy('txtProjectName')
            ->get();

        return view('reports.daily-plan-detail', [
            'dailyPlan' => $dailyPlan,
            'activitiesByDay' => $activitiesByDay,
            'days' => $days,
            'projects' => $projects,
            'authUser' => $authUser,
        ]);
    }

    public function destroy(MWeeklyPlan $dailyPlan): RedirectResponse
    {
        $authUser = MUser::find(session('auth_user_id'));
        if ($authUser && ! $authUser->isSuperadmin() && $dailyPlan->intUser_ID !== $authUser->intUser_ID) {
            abort(403, 'Unauthorized action.');
        }

        $dailyPlan->activities()->delete();
        $dailyPlan->delete();

        return redirect()->route('reports.daily-plans')->with('success', 'Weekly Plan Card berhasil dihapus.');
    }

    public function storeActivity(Request $request, MWeeklyPlan $dailyPlan): RedirectResponse
    {
        $authUser = MUser::find(session('auth_user_id'));
        if ($authUser && ! $authUser->isSuperadmin() && $dailyPlan->intUser_ID !== $authUser->intUser_ID) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'txtDayName' => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat'],
            'dtmActivityDate' => ['nullable', 'date'],
            'txtActivityName' => ['required', 'string', 'max:255'],
            'txtStartTime' => ['required', 'string', 'max:10'],
            'txtEndTime' => ['required', 'string', 'max:10'],
            'floatDuration' => ['nullable', 'numeric', 'min:0'],
            'txtLocationType' => ['nullable', 'string', 'max:100'],
            'intProject_ID' => ['nullable', 'integer'],
            'txtRemarks' => ['nullable', 'string', 'max:500'],
        ]);

        TrDailyPlanActivity::create([
            'intWeeklyPlan_ID' => $dailyPlan->intWeeklyPlan_ID,
            'intProject_ID' => ! empty($validated['intProject_ID']) ? (int) $validated['intProject_ID'] : null,
            'txtDayName' => $validated['txtDayName'],
            'dtmActivityDate' => $validated['dtmActivityDate'] ?? null,
            'txtActivityName' => $validated['txtActivityName'],
            'txtStartTime' => $validated['txtStartTime'],
            'txtEndTime' => $validated['txtEndTime'],
            'floatDuration' => isset($validated['floatDuration']) ? (float) $validated['floatDuration'] : 2.0,
            'txtLocationType' => $validated['txtLocationType'] ?? null,
            'txtRemarks' => $validated['txtRemarks'] ?? null,
            'txtInsertedBy' => $authUser?->txtEmployeeName ?? 'System',
        ]);

        return redirect()->route('daily-plans.show', $dailyPlan)->with('success', 'Aktivitas berhasil ditambahkan ke hari ' . $validated['txtDayName'] . '.');
    }

    public function destroyActivity(TrDailyPlanActivity $activity): RedirectResponse
    {
        $authUser = MUser::find(session('auth_user_id'));
        if ($authUser && ! $authUser->isSuperadmin() && $activity->intUser_ID !== $authUser->intUser_ID) {
            abort(403, 'Unauthorized action.');
        }

        $planId = $activity->intWeeklyPlan_ID;
        $dayName = $activity->txtDayName;
        $activity->delete();

        return redirect()->route('daily-plans.show', $planId)->with('success', 'Aktivitas hari ' . $dayName . ' berhasil dihapus.');
    }
}
