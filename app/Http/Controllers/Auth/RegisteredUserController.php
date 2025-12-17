<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View|RedirectResponse
    {
        // --- START: Role Authorization Check ---
        if ( !in_array(Auth::user()->role, ['Admin', 'HR']) ) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Role Authorization Check ---

        // Search Logic
        $query = User::query()->with('approvers');

        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%"); 
            });
        }

        $users = $query->paginate(10)->withQueryString(); 

        // Dropdown Lists
        $departments = ['Warehouse', 'ICD', 'Yard & Rail', 'Truck', 'IT', 'Process', 'Software', 'Data Center', 'Media', 'Secondary Transport', 'Business Development', 'Sales & CS', 'QEHS', 'Admin & HR', 'Finance & Account', 'M&E', 'Management', 'M&R', 'Customs & Formalities','Corportate'];
        $positions = [
            'Managing Director',
            'Director',
            'COO',
            'CEO',
            'General Manager',
            'Manager',
            'Assistant Manager',
            'Sr Supervisor',
            'Supervisor',
            'Assistant Supervisor',
            'Staff',
            'General Worker',
            'Operator (forklift)',
            'Sr Operator (Forklift)',
            
        ];
        $locations = ['Yangon', 'Mandalay'];

        // Get Potential Approvers (Active Users with higher roles)
        $potential_approvers = User::where('status', 'active')
            ->where(function ($q) {
                $q->where('role', 'Admin')
                  ->orWhereIn('position', ['Manager', 'Assistant Manager', 'General Manager', 'Assistant General Manager', 'COO', 'CEO', 'Managing Director']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'position', 'department']);

        return view('pages.users.users', compact('users', 'departments', 'positions', 'locations', 'potential_approvers'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // ၁။ Validation စစ်ဆေးခြင်း
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'employee_id' => ['nullable', 'string', 'unique:users'],
            // Finger Print ID ကို User ကိုယ်တိုင်ရိုက်ထည့်မည်ဖြစ်သော်လည်း Unique ဖြစ်ရမည်
            'finger_print_id' => ['required', 'string', 'unique:users'],
            'location' => ['required', 'string'],
            'department' => ['nullable', 'string'],
            'position' => ['nullable', 'string'],
            'role' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'approvers' => ['nullable', 'array'],
            'approvers.*' => ['exists:users,id'],
        ]);

        // ၂။ User Create လုပ်ခြင်း
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $request->employee_id,
            'finger_print_id' => $request->finger_print_id, // Form မှလာသော ID အတိုင်းသိမ်းမည်
            'phone' => $request->phone,
            'location' => $request->location,
            'department' => $request->department,
            'position' => $request->position,
            'role' => $request->role,
            'status' => $request->status,
            'can_request_ot' => $request->has('can_request_ot') ? 1 : 0,
            'morning_ot' => $request->has('morning_ot') ? 1 : 0,
        ]);

        // ၃။ Approvers များ ချိတ်ဆက်ခြင်း
        if ($request->has('approvers')) {
            $user->approvers()->sync($request->approvers);
        }

        return redirect()->back()->with('success', 'User created successfully with ID: ' . $user->finger_print_id);
    }

    /**
     * Update the specified user's profile information.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
         if ( !in_array(Auth::user()->role, ['Admin', 'HR']) ) {
            return redirect()->back()->with('error', 'You do not have permission.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'employee_id' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'finger_print_id' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
            'can_request_ot' => ['nullable'],
            'morning_ot' => ['nullable'],
            'approvers' => ['nullable', 'array'],
            'approvers.*' => ['exists:users,id'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'finger_print_id' => $request->finger_print_id,
            'phone' => $request->phone,
            'location' => $request->location,
            'department' => $request->department,
            'position' => $request->position,
            'role' => $request->role,
            'status' => $request->status,
            'can_request_ot' => $request->has('can_request_ot') ? 1 : 0,
            'morning_ot' => $request->has('morning_ot') ? 1 : 0,
        ]);

        // [Update Logic] Sync what is sent from the form
        if ($request->has('approvers')) {
            $user->approvers()->sync($request->approvers);
        } else {
             // If creating form field exists but no value selected, clear approvers
             if ($request->exists('approvers')) {
                 $user->approvers()->detach();
             }
        }

        return redirect()->back()->with('success', 'User details updated successfully!');
    }

    /**
     * Update the specified user's password.
     */
    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        if (Auth::user()->role !== 'Admin') {
            return redirect()->back()->with('error', 'Only Admins can reset passwords.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }
}