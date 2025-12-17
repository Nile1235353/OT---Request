<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtRequest;
use App\Models\assignTeam;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Exports\EmployeeOtExport; // <-- 1. Import Export Class
use Maatwebsite\Excel\Facades\Excel; // <-- 2. Import Excel Facade
use App\Models\OtAttendance;

class OtRequestController extends Controller
{
    // My OT Request Methods Here

    // public function myotView()
    // {
    //     return view('pages.myOt.myot');
    // }

    /**
     * Display the user's assigned OT jobs and total monthly hours, with filtering.
     * * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    /**
     * Display the user's assigned OT jobs and total monthly hours.
     */
    public function myotView(Request $request)
    {
        $user = Auth::user();

        $currentMonth = (int) $request->input('month', now()->month);
        $currentYear = (int) $request->input('year', now()->year);

        // 1. Total Approved Actual Hours Calculation
        $approvedOtDates = OtRequest::where('status', 'approved')
            ->whereMonth('ot_date', $currentMonth)
            ->whereYear('ot_date', $currentYear)
            ->whereHas('assignedUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->pluck('ot_date')
            ->unique();

        $totalMonthlyHours = 0;
        
        // [FIX] user->employee_id အစား user->finger_print_id ကို အသုံးပြုပါ
        // OtAttendance table ထဲမှာ Fingerprint ID (e.g., 57) နဲ့ သိမ်းထားလို့ပါ
        if ($user->finger_print_id && $approvedOtDates->isNotEmpty()) {
            $totalMonthlyHours = OtAttendance::where('employee_id', $user->finger_print_id)
                ->whereIn('date', $approvedOtDates)
                ->sum('actual_ot_hours');
        }

        // 2. Assigned OT Jobs
        $assignedJobs = AssignTeam::where('user_id', $user->id)
            ->whereHas('otRequest', function ($query) use ($currentMonth, $currentYear) {
                $query->whereMonth('ot_date', $currentMonth)
                    ->whereYear('ot_date', $currentYear);
            })
            ->with('otRequest')
            ->latest('created_at')
            ->get();

        // 3. Fingerprint Actual Data for Table View
        $attendanceRecords = [];
        
        // [FIX] ဒီနေရာမှာလည်း finger_print_id ကိုပဲ သုံးပါ
        if ($user->finger_print_id) {
            $attendanceRecords = OtAttendance::where('employee_id', $user->finger_print_id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->get()
                ->keyBy('date'); 
        }

        return view('pages.myOt.myot', compact(
            'totalMonthlyHours', 
            'assignedJobs', 
            'currentMonth', 
            'currentYear',
            'attendanceRecords'
        ));
    }

    public function acknowledge(AssignTeam $job)
    {
        // [FIXED] Server ပေါ်တွင် String/Integer ကွဲလွဲမှုကို ဖြေရှင်းရန် (int) ပြောင်းပြီး စစ်ဆေးပါ
        if ((int)$job->user_id !== (int)Auth::id()) {
            abort(403);
        }

        $job->update(['employee_status' => 'acknowledged']);

        return redirect()->back()->with('success', 'OT job has been acknowledged!');
    }

    // Request OT Methods here

    // public function requestOtView()
    // {
    //     return view('pages.requestOt.requestot');
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // --- START: Authorization Check ---
        $user = Auth::user();
        
        // Role/Position စစ်ဆေးခြင်း
        $isManagerLevel = in_array($user->position, ['Supervisor', 'Assistant Supervisor', 'Manager', 'Assistant Manager', 'General Manager', 'Assistant General Manager',]);
        
        // can_request_ot permission ရှိမရှိ စစ်ဆေးခြင်း (1 ဖြစ်မှ ခွင့်ပြုမည်)
        $hasPermission = $user->can_request_ot == 1;

        // Admin မဟုတ်၊ Manager Level မဟုတ်၊ Permission လည်းမရှိလျှင် ပိတ်မည်
        if ($user->role !== 'Admin' && !$isManagerLevel && !$hasPermission) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Authorization Check ---

        // Dropdown Lists
        $departments = User::distinct()->pluck('department')->filter();
        
        // Initial employees (Logged in user's dept)
        $employees = User::where('department', $user->department)->get();

        // Login ဝင်ထားတဲ့ User ရဲ့ Department တစ်ခုလုံးက တင်ထားတဲ့ Request အားလုံးကို ဆွဲထုတ်ခြင်း
        $query = OtRequest::whereHas('supervisor', function($q) use ($user) {
            $q->where('department', $user->department);
        });

        // Apply Filters
        if (request('filter_month')) {
            $query->whereMonth('ot_date', request('filter_month'));
        }
        if (request('filter_year')) {
            $query->whereYear('ot_date', request('filter_year'));
        }
        if (request('filter_request_id')) {
            $query->where('request_id', 'like', '%' . request('filter_request_id') . '%');
        }
        if (request('filter_customer')) {
            $query->where('customer_name', 'like', '%' . request('filter_customer') . '%');
        }

        $myRequests = $query->with(['supervisor', 'assignTeams.user']) 
            ->orderBy('ot_date', 'desc')
            ->limit(50)
            ->get();

        return view('pages.requestOt.requestot', compact('departments', 'employees', 'myRequests'));
    }

    public function getEmployeesByDept(Request $request)
    {
        $dept = $request->department;
        if($dept == 'All') {
            $employees = User::all(['id', 'name', 'employee_id', 'department']);
        } else {
            $employees = User::where('department', $dept)->get(['id', 'name', 'employee_id', 'department']);
        }
        return response()->json($employees);
    }

    public function store(Request $request)
    {
        // Authorization Check
        $user = Auth::user();
        
        // Position စစ်ဆေးခြင်း
        $isManagerLevel = in_array($user->position, ['Supervisor', 'Assistant Supervisor', 'Manager', 'Assistant Manager']);
        
        // [ပြင်ဆင်ချက်] can_request_ot permission ရှိမရှိ စစ်ဆေးခြင်း
        $hasPermission = $user->can_request_ot == 1;

        // Admin မဟုတ်၊ Manager Level မဟုတ်၊ Permission လည်းမရှိလျှင် Submit လုပ်ခွင့်မရှိပါ
        if ($user->role !== 'Admin' && !$isManagerLevel && !$hasPermission) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        $request->validate([
            'ot_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $inputDate = Carbon::parse($value)->startOfDay();
                    $limitDate = Carbon::today()->subDays(3)->startOfDay(); 

                    if ($inputDate->lt($limitDate)) {
                        $fail('လွန်ခဲ့သော ၃ ရက်ထက် ကျော်လွန်သည့် ရက်ဟောင်းများကို OT တင်ခွင့်မပြုပါ။');
                    }
                },
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'total_hours' => 'required|numeric|min:0.5', 
            'requirement_type' => 'required|string',
            'customer_name' => 'nullable|string|max:100',
            'job_code' => 'nullable|string|max:50', 
            'reason' => 'required|string',
            'team_members' => 'required|array|min:1',
            'team_members.*' => 'exists:users,id',
            'tasks' => 'required|array',
            'tasks.*' => 'required|string|max:255',
        ]);

        // Duplicate Check
        $duplicateUsers = assignTeam::whereIn('user_id', $request->team_members)
            ->whereHas('otRequest', function ($query) use ($request) {
                $query->where('ot_date', $request->ot_date)
                      ->where('status', '!=', 'rejected'); 
            })
            ->with('user') 
            ->get()
            ->pluck('user.name') 
            ->unique()
            ->toArray();

        if (!empty($duplicateUsers)) {
            $names = implode(', ', $duplicateUsers);
            return redirect()->back()
                ->withInput() 
                ->with('error', "ရွေးချယ်ထားသော ရက်စွဲ ({$request->ot_date}) တွင် အောက်ပါဝန်ထမ်းများအတွက် OT ရှိပြီးသား ဖြစ်နေပါသည်: {$names}");
        }

        DB::transaction(function () use ($request) {
            do {
                $newRequestId = 'OT-' . date('Ym') . '-' . mt_rand(100, 999);
            } while (OtRequest::where('request_id', $newRequestId)->exists());

            $otRequest = OtRequest::create([
                'request_id' => $newRequestId,
                'supervisor_id' => auth()->id(),
                'job_code' => $request->job_code,
                'customer_name' => $request->customer_name,
                'ot_date' => $request->ot_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time, 
                'total_hours' => $request->total_hours, 
                'requirement_type' => $request->requirement_type,
                'reason' => $request->reason,
                'status' => 'pending', 
            ]);

            foreach ($request->team_members as $memberId) {
                if (isset($request->tasks[$memberId])) {
                    assignTeam::create([
                        'ot_requests_id' => $otRequest->id,
                        'user_id' => $memberId,
                        'task_description' => $request->tasks[$memberId],
                    ]);
                }
            }
        });

        return redirect()->route('overtime.create')->with('success', 'Overtime request submitted successfully!');
    }

    // Approve OT
/**
     * Display the OT approval page with pending and history requests.
     */
    public function otApprove(): View|RedirectResponse
    {
        $user = Auth::user();
        
        // Admin Check
        $isAdmin = strtolower($user->role ?? '') === 'admin';

        // --- 1. Fetch PENDING requests ---
        $pendingRequests = OtRequest::with('supervisor.approvers', 'assignedUsers.user')
            ->where('status', 'pending')
            ->where('supervisor_id', '!=', $user->id) // Self-Approval Prevention
            ->where(function ($query) use ($user, $isAdmin) {
                // (A) Strict Approver Check
                $query->whereHas('supervisor.approvers', function ($q) use ($user) {
                    $q->where('approver_user.approver_id', $user->id); 
                });

                // (B) Admin Override
                if ($isAdmin) {
                    $query->orWhereRaw('1 = 1');
                }
            })
            ->latest()
            ->get();


        // --- 2. Fetch HISTORY requests ---
        $historyRequests = OtRequest::with('supervisor')
            ->whereIn('status', ['approved', 'rejected'])
            ->where(function ($query) use ($user, $isAdmin) {
                // (A) History for Explicit Approver
                $query->whereHas('supervisor.approvers', function ($q) use ($user) {
                    $q->where('approver_user.approver_id', $user->id);
                });

                // (B) Admin sees all history
                if ($isAdmin) {
                    $query->orWhereRaw('1 = 1');
                }
            })
            ->latest()
            ->take(10)
            ->get();

        // --- 3. [UPDATED] Get Data for "Add New User" Dropdowns ---
        
        // (A) Get All Departments (Unique & Sorted)
        $departments = User::where('status', 'active')
                           ->whereNotNull('department')
                           ->where('department', '!=', '')
                           ->distinct()
                           ->pluck('department')
                           ->sort()
                           ->values();

        // (B) Get ALL Users (For JS Filtering)
        // Approver ရဲ့ Department တစ်ခုတည်းမဟုတ်ဘဲ အကုန်ယူပါမယ်
        $allUsers = User::where('status', 'active')
                        ->select('id', 'name', 'department')
                        ->orderBy('name', 'asc')
                        ->get();

        return view('pages.approveOt.approveot', compact('pendingRequests', 'historyRequests', 'departments', 'allUsers'));
    }

    /**
     * Approve an OT request with potential modifications to assigned users.
     */
    public function approve(Request $request, OtRequest $otRequest)
    {
        $user = Auth::user();
        
        // --- Authorization Check ---
        $isExplicitApprover = $otRequest->supervisor->approvers()
                                        ->where('approver_user.approver_id', $user->id)
                                        ->exists();
        $isAdmin = (strtolower($user->role ?? '') === 'admin');
        $isNotSelf = ($otRequest->supervisor_id !== $user->id);

        if (!$isNotSelf || (! $isAdmin && ! $isExplicitApprover)) {
            return redirect()->back()->with('error', 'You do not have permission to approve this request.');
        }

        // --- Update Logic ---
        // 1. Update Existing Users
        if ($request->has('users')) {
            foreach ($request->users as $userId => $data) {
                $assignedUser = assignTeam::where('ot_requests_id', $otRequest->id)
                                          ->where('user_id', $userId)
                                          ->first();
                if ($assignedUser) {
                    if (isset($data['remove']) && $data['remove'] == '1') {
                        $assignedUser->delete();
                    } else {
                        $assignedUser->task_description = $data['task_description'];
                        $assignedUser->save();
                    }
                }
            }
        }

        // 2. Add New Users
        if ($request->has('new_users')) {
            foreach ($request->new_users as $userId => $data) {
                if (!empty($data['id']) && !empty($data['task_description'])) {
                    assignTeam::updateOrCreate(
                        [
                            'ot_requests_id' => $otRequest->id,
                            'user_id' => $data['id'],
                        ],
                        [
                            'task_description' => $data['task_description'],
                        ]
                    );
                }
            }
        }

        $otRequest->status = 'approved';
        $otRequest->save();

        return redirect()->back()->with('success', 'OT Request has been approved with changes.');
    }

    /**
     * Reject an OT request.
     */
    public function reject(Request $request, OtRequest $otRequest)
    {
        $user = Auth::user();

        // --- Authorization Check ---
        $isExplicitApprover = $otRequest->supervisor->approvers()
                                        ->where('approver_user.approver_id', $user->id)
                                        ->exists();
        $isAdmin = (strtolower($user->role ?? '') === 'admin');
        $isNotSelf = ($otRequest->supervisor_id !== $user->id);

        if (!$isNotSelf || (! $isAdmin && ! $isExplicitApprover)) {
            return redirect()->back()->with('error', 'You do not have permission to reject this request.');
        }

        $request->validate([
            'reject_remark' => 'required|string|max:1000',
        ]);

        $otRequest->update([
            'status' => 'rejected',
            'reject_remark' => $request->reject_remark,
        ]);

        return redirect()->back()->with('success', 'OT Request has been rejected successfully.');
    }

    /**
     * Display the employee OT report (Actual Data Only).
     */
    public function employeeOtReport(Request $request)
    {
        $currentUser = Auth::user();

        // 1. Authorization Check (Gatekeeper)
        // Admin, HR, Management Roles သို့မဟုတ် Manager, GM, AGM, CEO, COO Position ရှိသူများသာ ဝင်ကြည့်ခွင့်ရှိသည်။
        $authorizedRoles = ['Admin', 'HR', 'Management'];
        
        // Layout နဲ့ ကိုက်ညီအောင် Position များကို စုံလင်စွာ ထည့်သွင်းထားပါသည်
        $allowedPositions = [
            'Manager', 
            'Assistant Manager', 
            'General Manager', 
            'Assistant General Manager', 
            'CEO', 
            'COO'
        ];

        // Role မဟုတ်ဘူး၊ Allowed Position လည်း မဟုတ်ဘူးဆိုရင် ဝင်ခွင့်ပိတ်မယ်
        if (!in_array($currentUser->role, $authorizedRoles) && !in_array($currentUser->position, $allowedPositions)) {
            return redirect()->back()->with('error', 'You do not have permission to access this report.');
        }

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'department' => 'nullable|string',
            'requirement_type' => 'nullable|string',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $department = $request->input('department');
        $requirementType = $request->input('requirement_type');

        // 2. Base Query
        $query = AssignTeam::with(['user', 'otRequest', 'otRequest.supervisor'])
            ->join('ot_requests', 'assign_teams.ot_requests_id', '=', 'ot_requests.id')
            ->join('users', 'assign_teams.user_id', '=', 'users.id')
            ->where('ot_requests.status', 'Approved');

        // 3. Data Filtering Logic
        // Admin/HR/Management Role မဟုတ်ရင် (ဆိုလိုတာက Manager/AGM/GM/CEO/COO Position သမားဖြစ်နေရင်) သူ့ Department ပဲ ပြမယ်
        if (!in_array($currentUser->role, $authorizedRoles)) {
            $query->where('users.department', $currentUser->department);
        }

        // 4. Apply Filters
        if ($startDate) {
            $query->where('ot_requests.ot_date', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate) {
            $query->where('ot_requests.ot_date', '<=', Carbon::parse($endDate)->endOfDay());
        }
        
        // Department Filter (Dropdown က ရွေးလိုက်ရင် ထပ်စစ်မယ်)
        if ($department) {
            $query->where('users.department', $department);
        }
        
        if ($requirementType) {
            $query->where('ot_requests.requirement_type', $requirementType);
        }

        // 5. Get Data
        $assignedOts = $query->orderBy('ot_requests.ot_date', 'desc')->select('assign_teams.*')->get();

        // Dropdown Data
        $requirementTypes = OtRequest::distinct()->pluck('requirement_type')->filter()->values();

        // 6. Actual Data Mapping
        $fingerPrintIds = $assignedOts->map(fn($item) => $item->user->finger_print_id)->filter()->unique();
        $dates = $assignedOts->map(fn($item) => $item->otRequest->ot_date)->unique();

        $attendanceRecords = [];
        if ($fingerPrintIds->isNotEmpty()) {
            $attendanceRecords = OtAttendance::whereIn('employee_id', $fingerPrintIds)
                ->whereIn('date', $dates)
                ->get()
                ->keyBy(fn($item) => $item->employee_id . '_' . $item->date);
        }

        $totalActualHours = 0;
        foreach ($assignedOts as $assignment) {
            $fpId = $assignment->user->finger_print_id;
            $otDate = $assignment->otRequest->ot_date;
            $key = $fpId . '_' . $otDate;

            if (isset($attendanceRecords[$key])) {
                $attendance = $attendanceRecords[$key];
                $assignment->actual_hours = $attendance->actual_ot_hours;
                $assignment->actual_in = $attendance->check_in_time;
                $assignment->actual_out = $attendance->check_out_time;
                $totalActualHours += $attendance->actual_ot_hours;
            } else {
                $assignment->actual_hours = 0;
                $assignment->actual_in = null;
                $assignment->actual_out = null;
            }
        }

        return view('pages.employeeOtReport.employeeot', [
            'assignedOts' => $assignedOts,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'department' => $department,
                'requirement_type' => $requirementType,
            ],
            'totalHours' => $totalActualHours,
            'requirementTypes' => $requirementTypes,
        ]);
    }

    /**
     * [NEW] Update Task Description
     */
    public function updateTask(Request $request, $id)
    {
        $request->validate([
            'task_description' => 'required|string|max:255',
        ]);

        // AssignTeam model ကို ရှာပြီး update လုပ်ပါမယ်
        // Note: $id သည် assign_teams table ၏ id ဖြစ်ရပါမည်
        $assignment = assignTeam::findOrFail($id);
        
        $assignment->update([
            'task_description' => $request->task_description
        ]);

        return redirect()->back()->with('success', 'Task updated successfully!');
    }

    /**
     * Handle the Excel export request.
     */
    public function exportEmployeeOt(Request $request)
    {
        // Export မထုတ်ခင် Permission အရင်စစ်ပါတယ်
        $currentUser = Auth::user();
        $authorizedRoles = ['Admin', 'HR', 'Management'];
        $isManager = $currentUser->position === 'Manager';

        if (!in_array($currentUser->role, $authorizedRoles) && !$isManager) {
             return redirect()->back()->with('error', 'You do not have permission to export.');
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $department = $request->input('department'); // [NEW] Get department
        $requirementType = $request->input('requirement_type'); // [NEW] Get requirement type

        $fileName = 'Employee_OT_Actual_Report_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new EmployeeOtExport($startDate, $endDate, $department), $fileName);
    }
}
