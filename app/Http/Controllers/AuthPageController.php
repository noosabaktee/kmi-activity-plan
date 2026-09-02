<?php

namespace App\Http\Controllers;

use App\Models\MDepartment;
use App\Models\MProject;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Support\RoleAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Throwable;

class AuthPageController extends Controller
{
    public function login(): View
    {
        return view('auth.login', [
            'loginSummary' => $this->loginSummary(),
        ]);
    }

    private function loginSummary(): array
    {
        try {
            $totalProjects = MProject::where('bitActive', true)->count();
            $totalEmployees = MUser::where('bitActive', true)->where('txtRole', RoleAccess::ROLE_EMPLOYEE)->count();
            $avgActual = round((float) MProject::where('bitActive', true)->avg('floatActual'), 1);

            return [
                'totalProjects' => $totalProjects ?: 10,
                'totalEmployees' => $totalEmployees ?: 12,
                'avgActual' => $avgActual ?: 78.5,
                'departmentName' => 'Manufacturing Development & Planning (MDP)',
                'year' => now()->year,
            ];
        } catch (Throwable) {
            return [
                'totalProjects' => 10,
                'totalEmployees' => 12,
                'avgActual' => 78.5,
                'departmentName' => 'Manufacturing Development & Planning (MDP)',
                'year' => now()->year,
            ];
        }
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'txtEmail' => ['required', 'email'],
            'txtPassword' => ['required', 'string'],
        ]);

        $user = MUser::where('txtEmail', $credentials['txtEmail'])
            ->where('bitActive', true)
            ->first();

        if (! $user || ! Hash::check($credentials['txtPassword'], $user->txtPassword)) {
            return back()
                ->withErrors(['txtEmail' => 'Email atau kata sandi tidak cocok.'])
                ->onlyInput('txtEmail');
        }

        $request->session()->regenerate();
        $request->session()->put('auth_user_id', $user->intUser_ID);

        return redirect()->route('dashboard.index');
    }

    public function register(): View
    {
        $departments = MDepartment::where('bitActive', true)->get();
        $subDepartments = MSubDepartment::where('bitActive', true)->get();
        $roles = RoleAccess::roles();

        return view('auth.register', compact('departments', 'subDepartments', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'txtEmployeeName' => ['required', 'string', 'max:255'],
            'txtEmployeeCode' => ['nullable', 'string', 'max:50'],
            'txtEmail' => ['required', 'email', 'max:255', Rule::unique('mUser', 'txtEmail')],
            'txtPassword' => ['required', 'string', 'min:6', 'confirmed'],
            'txtPhone' => ['required', 'string', 'max:30'],
            'txtRole' => ['required', Rule::in(RoleAccess::roles())],
            'intDepartment_ID' => ['nullable', 'exists:mDepartment,intDepartment_ID'],
            'intSubDepartment_ID' => ['nullable', 'exists:mSubDepartment,intSubDepartment_ID'],
            'txtPosition' => ['nullable', 'string', 'max:100'],
        ]);

        $now = now();
        $user = MUser::create([
            'intDepartment_ID' => $validated['intDepartment_ID'] ?? 1,
            'intSubDepartment_ID' => $validated['intSubDepartment_ID'] ?? null,
            'txtEmployeeCode' => $validated['txtEmployeeCode'] ?? 'EMP-' . rand(100, 999),
            'txtEmployeeName' => $validated['txtEmployeeName'],
            'txtEmail' => $validated['txtEmail'],
            'txtPassword' => Hash::make($validated['txtPassword']),
            'txtPhone' => $validated['txtPhone'],
            'txtRole' => $validated['txtRole'],
            'txtPosition' => $validated['txtPosition'] ?? $validated['txtRole'],
            'bitActive' => true,
            'txtInsertedBy' => 'register',
            'dtmInserted' => $now,
        ]);

        $request->session()->regenerate();
        $request->session()->put('auth_user_id', $user->intUser_ID);

        return redirect()->route('dashboard.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('auth_user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
