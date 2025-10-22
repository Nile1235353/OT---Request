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

class OtRequestController extends Controller
{
    // My OT Request Methods Here

    // public function myotView()
    // {
    //     return view('pages.myOt.myot');
    // }

    public function myotView()
    {
        $user = Auth::user();

        // 1. ဒီလအတွက် Approve ဖြစ်ပြီးသား စုစုပေါင်း OT နာရီကိုရှာခြင်း
        $totalMonthlyHours = OtRequest::where('status', 'approved')
            ->whereMonth('ot_date', now()->month)
            ->whereYear('ot_date', now()->year)
            ->whereHas('assignedUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->sum('total_hours');

        // 2. Login ဝင်ထားတဲ့သူကို assign လုပ်ထားတဲ့ OT Job တွေအားလုံးကိုရှာခြင်း
        $assignedJobs = AssignTeam::with('otRequest')
            ->where('user_id', $user->id)
            ->latest('created_at') // အသစ်ဆုံးကိုအပေါ်မှာထားရန်
            ->get();
            
        return view('pages.myOt.myot', compact('totalMonthlyHours', 'assignedJobs'));
    }

    // "Acknowledge" button နှိပ်တာကို ကိုင်တွယ်ရန်
    public function acknowledge(AssignTeam $job)
    {
        // Policy or check to ensure the user can only acknowledge their own job
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // --- START: Authorization Check ---
        $user = Auth::user();
        $isManagerLevel = in_array($user->position, ['Supervisor', 'Assistant Supervisor', 'Manager']);

        // User's role is NOT Admin AND their position is NOT Manager Level
        if ($user->role !== 'Admin' && !$isManagerLevel) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Authorization Check ---

        $request->validate([
            // 'supervisor_id' => 'required|exists:users,id',
            'ot_date' => 'required|date',
            'total_hours' => 'required|numeric|min:0.5',
            'requirement_type' => 'required|string',
            'reason' => 'required|string',
            'team_members' => 'required|array|min:1',
            'team_members.*' => 'exists:users,id',
            'tasks' => 'required|array',
            'tasks.*' => 'required|string|max:255',
        ]);

        // Use DB Transaction to ensure data integrity
        DB::transaction(function () use ($request) {
            // Step 1: Create the main Overtime Request
            $otRequest = OtRequest::create([
                'request_id' => 'OT-' . date('Ym') . '-' . mt_rand(100, 999), // Generate a unique ID
                'supervisor_id' => auth()->id(),
                'ot_date' => $request->ot_date,
                'total_hours' => $request->total_hours,
                'requirement_type' => $request->requirement_type,
                'reason' => $request->reason,
                'status' => 'pending', // Initial status
            ]);

            // Step 2: Loop through assigned members and save them to the pivot table
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
     * Reject an OT request.
     */
    public function reject(OtRequest $otRequest)
    {
        // --- START: Authorization Check ---
        $user = Auth::user();
        $isManagerLevel = in_array($user->position, ['Manager']);

        // User's role is NOT Admin AND their position is NOT Manager Level
        if ($user->role !== 'Admin' && !$isManagerLevel) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Authorization Check ---

        $otRequest->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'OT Request has been rejected.');
    }
}
