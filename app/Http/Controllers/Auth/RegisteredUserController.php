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

        // [UPDATED] Search Logic
        $query = User::query();

        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%"); // ID နဲ့ပါ ရှာလို့ရအောင် ထည့်ပေးထားပါတယ်
            });
        }

        // [FIX] Use paginate instead of get() to prevent timeout with large datasets
        // Adding withQueryString() keeps the search term when changing pages
        $users = $query->paginate(10)->withQueryString(); 

        // Dropdown Lists
        $departments = ['Warehouse', 'BD', 'SCS', 'Data Center', 'ICD', 'CCA', 'IT', 'IT & Process', 'M&E', 'M&R', 'QEHS', 'HR', 'Corporate','Truck','Yard & Rail', 'Process', 'Finance'];
        $positions = ['Manager','Assistant Supervisor', 'Supervisor', 'Staff'];
        $locations = ['Yangon', 'Mandalay', 'Nay Pyi Taw', 'Taunggyi', 'Mawlamyine'];

        return view('pages.users.users', compact('users', 'departments', 'positions', 'locations'));
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // --- START: Role Authorization Check ---
        if ( !in_array(Auth::user()->role, ['Admin', 'HR']) ) {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Role Authorization Check ---

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'employee_id' => ['nullable', 'string', 'max:255', 'unique:'.User::class],
            'finger_print_id' => ['nullable', 'string', 'max:50', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'can_request_ot' => ['nullable'],
            'morning_ot' => ['nullable'], 
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $request->employee_id,
            'finger_print_id' => $request->finger_print_id,
            'phone' => $request->phone,
            'location' => $request->location,
            'role' => $request->role ?? 'user',
            'department' => $request->department,
            'position' => $request->position,
            'can_request_ot' => $request->has('can_request_ot') ? 1 : 0,
            'morning_ot' => $request->has('morning_ot') ? 1 : 0, 
        ]);

        event(new Registered($user));

        return redirect()->route('users.create')->with('success', 'User created successfully!');
    }

    /**
     * Update the specified user's profile information.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // --- Role Check ---
        if ( !in_array(Auth::user()->role, ['Admin', 'HR']) ) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'employee_id' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'finger_print_id' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'can_request_ot' => ['nullable'],
            'morning_ot' => ['nullable'],
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
            'can_request_ot' => $request->has('can_request_ot') ? 1 : 0,
            'morning_ot' => $request->has('morning_ot') ? 1 : 0,
        ]);

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