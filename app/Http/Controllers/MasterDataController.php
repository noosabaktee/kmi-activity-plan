<?php

namespace App\Http\Controllers;

use App\Models\MDepartment;
use App\Models\MProjectType;
use App\Models\MSubDepartment;
use App\Models\MUser;
use App\Models\TrSupervisorSubDept;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'departments');

        $departments = MDepartment::withCount(['subDepartments', 'users', 'projects'])->active()->get();
        $subDepartments = MSubDepartment::with(['department'])->withCount(['users', 'projects'])->active()->get();
        $projectTypes = MProjectType::withCount(['projects'])->active()->get();
        $users = MUser::with(['department', 'subDepartment', 'supervisedSubDepartments'])->active()->orderBy('txtRole')->orderBy('txtEmployeeName')->get();

        return view('master.index', [
            'tab' => $tab,
            'departments' => $departments,
            'subDepartments' => $subDepartments,
            'projectTypes' => $projectTypes,
            'users' => $users,
        ]);
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'txtDepartmentCode' => ['required', 'string', 'max:50', 'unique:mDepartment,txtDepartmentCode'],
            'txtDepartmentName' => ['required', 'string', 'max:200'],
            'txtDescription' => ['nullable', 'string'],
        ]);

        $authUser = MUser::find(session('auth_user_id'));

        MDepartment::create([
            'txtDepartmentCode' => strtoupper($validated['txtDepartmentCode']),
            'txtDepartmentName' => $validated['txtDepartmentName'],
            'txtDescription' => $validated['txtDescription'] ?? null,
            'txtInsertedBy' => $authUser?->txtEmployeeName ?? 'System',
        ]);

        return redirect()->route('master.index', ['tab' => 'departments'])->with('success', 'Department berhasil ditambahkan.');
    }

    public function storeSubDepartment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'intDepartment_ID' => ['required', 'integer'],
            'txtSubDepartmentCode' => ['required', 'string', 'max:50'],
            'txtSubDepartmentName' => ['required', 'string', 'max:200'],
            'txtDescription' => ['nullable', 'string'],
        ]);

        $authUser = MUser::find(session('auth_user_id'));

        MSubDepartment::create([
            'intDepartment_ID' => $validated['intDepartment_ID'],
            'txtSubDepartmentCode' => strtoupper($validated['txtSubDepartmentCode']),
            'txtSubDepartmentName' => $validated['txtSubDepartmentName'],
            'txtDescription' => $validated['txtDescription'] ?? null,
            'txtInsertedBy' => $authUser?->txtEmployeeName ?? 'System',
        ]);

        return redirect()->route('master.index', ['tab' => 'subdepartments'])->with('success', 'Sub Department berhasil ditambahkan.');
    }

    public function storeProjectType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'txtProjectTypeCode' => ['required', 'string', 'max:50', 'unique:mProjectType,txtProjectTypeCode'],
            'txtProjectTypeName' => ['required', 'string', 'max:200'],
            'floatDefaultWeight' => ['nullable', 'numeric'],
            'txtColor' => ['nullable', 'string', 'max:20'],
        ]);

        $authUser = MUser::find(session('auth_user_id'));

        MProjectType::create([
            'txtProjectTypeCode' => strtoupper($validated['txtProjectTypeCode']),
            'txtProjectTypeName' => $validated['txtProjectTypeName'],
            'floatDefaultWeight' => $validated['floatDefaultWeight'] ?? 20.0,
            'txtColor' => $validated['txtColor'] ?? '#006838',
            'txtInsertedBy' => $authUser?->txtEmployeeName ?? 'System',
        ]);

        return redirect()->route('master.index', ['tab' => 'project_types'])->with('success', 'Tipe Project KPI berhasil ditambahkan.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'txtEmployeeName' => ['required', 'string', 'max:200'],
            'txtEmployeeCode' => ['nullable', 'string', 'max:50'],
            'txtEmail' => ['required', 'email', 'max:200', 'unique:mUser,txtEmail'],
            'txtPhone' => ['required', 'string', 'max:50'],
            'txtRole' => ['required', 'string', 'in:Head,Supervisor,Employee,Superadmin'],
            'intDepartment_ID' => ['nullable', 'integer'],
            'intSubDepartment_ID' => ['nullable', 'integer'],
            'txtPassword' => ['required', 'string', 'min:6'],
            'supervised_subdepts' => ['nullable', 'array'],
        ]);

        $authUser = MUser::find(session('auth_user_id'));

        $user = MUser::create([
            'intDepartment_ID' => $validated['intDepartment_ID'] ?? 1,
            'intSubDepartment_ID' => $validated['intSubDepartment_ID'] ?? null,
            'txtEmployeeName' => $validated['txtEmployeeName'],
            'txtEmployeeCode' => $validated['txtEmployeeCode'] ?? null,
            'txtEmail' => strtolower($validated['txtEmail']),
            'txtPassword' => Hash::make($validated['txtPassword']),
            'txtPhone' => $validated['txtPhone'],
            'txtRole' => $validated['txtRole'],
            'txtInsertedBy' => $authUser?->txtEmployeeName ?? 'System',
        ]);

        // If supervisor has assigned sub-departments
        if ($user->isSupervisor() && ! empty($validated['supervised_subdepts'])) {
            foreach ($validated['supervised_subdepts'] as $subDeptId) {
                TrSupervisorSubDept::create([
                    'intUser_ID' => $user->intUser_ID,
                    'intSubDepartment_ID' => (int) $subDeptId,
                    'txtInsertedBy' => $authUser?->txtEmployeeName ?? 'System',
                ]);
            }
        }

        return redirect()->route('master.index', ['tab' => 'users'])->with('success', 'User ' . $user->txtEmployeeName . ' berhasil ditambahkan.');
    }
}
