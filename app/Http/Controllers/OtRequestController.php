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
        if ($job->user_id !== Auth::id()) {
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
        $isManagerLevel = in_array($user->position, ['Supervisor', 'Assistant Supervisor', 'Manager']);

        // User's role is NOT Admin AND their position is NOT Manager Level
        if ($user->role !== 'Admin' && !$isManagerLevel) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Authorization Check ---

        // Get users with specific roles to populate dropdowns
        $supervisors = User::whereIn('position', ['supervisor', 'manager'])->get();
        $userDepartment = Auth::user()->department;
        $employees = User::where('department', $userDepartment)->get();

        return view('pages.requestOt.requestot', compact('supervisors', 'employees'));
    }

    public function store(Request $request)
    {
        // --- START: Authorization Check ---
        $user = Auth::user();
        $isManagerLevel = in_array($user->position, ['Supervisor', 'Assistant Supervisor', 'Manager']);

        if ($user->role !== 'Admin' && !$isManagerLevel) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Authorization Check ---

        $request->validate([
            // OT Date Validation: ၃ ရက်ထက် ကျော်လွန်သော ရက်ဟောင်းများကို လက်မခံပါ
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
            'job_code' => 'nullable|string|max:50', 
            'reason' => 'required|string',
            'team_members' => 'required|array|min:1',
            'team_members.*' => 'exists:users,id',
            'tasks' => 'required|array',
            'tasks.*' => 'required|string|max:255',
        ]);

        // [NEW LOGIC] Check for duplicate OT requests on the same date
        // ရွေးချယ်ထားတဲ့ ဝန်ထမ်းတွေထဲက ဒီနေ့ရက်စွဲမှာ OT ရှိပြီးသားလူ (Rejected မဟုတ်သော) ရှိမရှိ စစ်မယ်
        $duplicateUsers = AssignTeam::whereIn('user_id', $request->team_members)
            ->whereHas('otRequest', function ($query) use ($request) {
                $query->where('ot_date', $request->ot_date)
                      ->where('status', '!=', 'rejected'); // Reject ဖြစ်ပြီးသားဆိုရင်တော့ ထပ်တင်လို့ရမယ်
            })
            ->with('user') // နာမည်ပြဖို့ User table နဲ့ချိတ်မယ်
            ->get()
            ->pluck('user.name') // နာမည်တွေကိုပဲ ယူမယ်
            ->unique()
            ->toArray();

        // တကယ်လို့ ရှိခဲ့ရင် Error ပြန်ပို့မယ်
        if (!empty($duplicateUsers)) {
            $names = implode(', ', $duplicateUsers);
            return redirect()->back()
                ->withInput() // ဖြည့်ထားတာတွေ မပျောက်အောင်
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
                    AssignTeam::create([
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
     * (ဒါက သင်ထည့်ခိုင်းတဲ့ function အသစ်ပါ)
     */
    public function otApprove(): View|RedirectResponse
    {
        // --- START: Authorization Check ---
        $user = Auth::user();
        $isManagerLevel = in_array($user->position, ['Manager', 'Admin', 'HR']); // Allow Admin and HR too

        if ($user->role !== 'Admin' && !$isManagerLevel) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Authorization Check ---

        // 1. Fetch PENDING requests FROM THE SAME DEPARTMENT
        $pendingRequests = OtRequest::with('supervisor', 'assignedUsers.user')
            ->where('status', 'pending')
            // This condition filters requests based on the requester's department
            ->whereHas('supervisor', function ($query) use ($user) {
                $query->where('department', $user->department);
            }) // <-- Add this block
            ->latest()
            ->get();

        // 2. Fetch HISTORY requests FROM THE SAME DEPARTMENT
        $historyRequests = OtRequest::with('supervisor')
            ->whereIn('status', ['approved', 'rejected'])
            // This condition filters requests based on the requester's department
            ->whereHas('supervisor', function ($query) use ($user) {
                $query->where('department', $user->department);
            }) // <-- Add this block
            ->latest()
            ->take(10) 
            ->get();

        // 3. --- ADDED ---
        // Login ဝင်ထားသူရဲ့ department ထဲက user တွေ အကုန်လုံးကို ဆွဲထုတ်ပါမယ်။
        // ဒါကို "Add New User" modal dropdown မှာ သုံးပါမယ်။
        $allUsers = User::where('department', $user->department)
                        ->orderBy('name', 'asc')
                        ->get();

        // 4. --- MODIFIED ---
        // $allUsers ကို view ဆီသို့ ထည့်ပို့ပေးပါမယ်။
        return view('pages.approveOt.approveot', compact('pendingRequests', 'historyRequests', 'allUsers'));
    }


    /**
     * Approve an OT request with potential modifications to assigned users.
     * (ဒါက မူလရှိပြီးသား approve logic function ပါ)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OtRequest  $otRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, OtRequest $otRequest)
    {
        // --- START: Authorization Check ---
        $user = Auth::user();
        $isManagerLevel = in_array($user->position, ['Manager']);

        // User's role is NOT Admin AND their position is NOT Manager Level
        if ($user->role !== 'Admin' && !$isManagerLevel) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Authorization Check ---

        // --- START: Update Logic from Modal Form ---

        // 1. Update or Remove existing users
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

        // 2. --- MODIFIED: Add new users (plural) ---
        // Logic ကို `exists()` check အစား ပိုမိုစိတ်ချရသော `updateOrCreate` ဖြင့် ပြောင်းလဲထားပါသည်။
        if ($request->has('new_users')) {
            foreach ($request->new_users as $userId => $data) {
                // Ensure task is not empty and ID is valid
                if (!empty($data['id']) && !empty($data['task_description'])) {
                    
                    // User ရှိ၊ မရှိ စစ်ဆေးပြီး မရှိလျှင် အသစ်ထည့်၊ ရှိလျှင် update လုပ်ပါမည်။
                    // (Soft-deleted ဖြစ်နေခဲ့လျှင်လည်း ၎င်းကို update လုပ်ပေးနိုင်ပါသည်)
                    assignTeam::updateOrCreate(
                        [
                            'ot_requests_id' => $otRequest->id,
                            'user_id' => $data['id'],
                        ],
                        [
                            'task_description' => $data['task_description'],
                            // If your model uses soft deletes, ensure 'deleted_at' is null
                            // 'deleted_at' => null 
                        ]
                    );
                }
            }
        }

        // 3. Finally, approve the main request
        $otRequest->status = 'approved';
        // $otRequest->approved_by = $user->id; 
        $otRequest->save();

        // --- END: Update Logic ---

        return redirect()->back()->with('success', 'OT Request has been approved with changes.');
    }

    // --- ADDED: New function to reverse approval ---
    /**
     * Reverse an approved OT request back to rejected.
     *
     * @param  \App\Models\OtRequest  $otRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    // public function reject(OtRequest $otRequest)
    // {
    //     // --- START: Authorization Check ---
    //     $user = Auth::user();
    //     $isManagerLevel = in_array($user->position, ['Manager', 'Admin', 'HR']);

    //     if ($user->role !== 'Admin' && !$isManagerLevel) {
    //         return redirect()->back()->with('error', 'You do not have permission to access this page.');
    //     }
    //     // --- END: Authorization Check ---

    //     // Only approved requests can be reversed
    //     if ($otRequest->status == 'approved') {
    //         $otRequest->status = 'rejected';
    //         // $otRequest->approved_by = null; // Optional: clear who approved it
    //         $otRequest->save();
            
    //         return redirect()->back()->with('success', 'OT approval has been reversed and set to rejected.');
    //     }

    //     return redirect()->back()->with('error', 'Only approved requests can be reversed.');
    // }
    /**
     * Reject an OT request with a reason.
     */
    public function reject(Request $request, OtRequest $otRequest)
    {
        // --- START: Authorization Check ---
        $user = Auth::user();
        $isManagerLevel = in_array($user->position, ['Manager']);

        if ($user->role !== 'Admin' && !$isManagerLevel) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Authorization Check ---

        // [NEW] Validate that a remark is provided
        $request->validate([
            'reject_remark' => 'required|string|max:1000',
        ]);

        // Update Status AND Remark
        $otRequest->update([
            'status' => 'rejected',
            'reject_remark' => $request->reject_remark, // Save the rejection reason
        ]);

        return redirect()->back()->with('success', 'OT Request has been rejected successfully.');
    }


    /**
     * Display the employee OT report (Actual Data Only).
     */
    public function employeeOtReport(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $currentUser = Auth::user();

        // 2. Query Approved Assignments
        $query = AssignTeam::with(['user', 'otRequest', 'otRequest.supervisor'])
            ->join('ot_requests', 'assign_teams.ot_requests_id', '=', 'ot_requests.id')
            ->join('users', 'assign_teams.user_id', '=', 'users.id')
            ->where('ot_requests.status', 'Approved');

        // Access Control Logic
        $isSuperUser = in_array($currentUser->role, ['Admin', 'HR']) || $currentUser->position === 'General Manager';

        if (!$isSuperUser) {
            $query->where('users.location', $currentUser->location)
                  ->where('users.department', $currentUser->department);
        }

        // 3. Apply Date Filters
        if ($startDate) {
            $query->where('ot_requests.ot_date', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate) {
            $query->where('ot_requests.ot_date', '<=', Carbon::parse($endDate)->endOfDay());
        }

        // 4. Get Data
        $assignedOts = $query->orderBy('ot_requests.ot_date', 'desc')
                             ->select('assign_teams.*')
                             ->get();

        // === START: ACTUAL DATA MAPPING ===
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
        // === END: ACTUAL DATA MAPPING ===

        return view('pages.employeeOtReport.employeeot', [
            'assignedOts' => $assignedOts,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'totalHours' => $totalActualHours,
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
        $assignment = AssignTeam::findOrFail($id);
        
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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $fileName = 'Employee_OT_Actual_Report_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new EmployeeOtExport($startDate, $endDate), $fileName);
    }
}
