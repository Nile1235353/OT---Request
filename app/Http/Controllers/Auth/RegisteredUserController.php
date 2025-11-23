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

        $users = User::all();

        // Dropdown Lists
        $departments = ['Warehouse', 'BD', 'SCS', 'Data Center', 'ICD', 'CCA', 'IT', 'IT & Process', 'M&E', 'M&R', 'QEHS', 'HR', 'Corporate','Truck','Yard & Rail', 'Process', 'Finance'];
        $positions = ['Manager','Assistant Supervisor', 'Supervisor', 'Staff'];
        
        // [NEW] Locations List
        $locations = ['Yangon', 'Mandalay', 'Nay Pyi Taw', 'Taunggyi', 'Mawlamyine'];

        return view('pages.users.users', compact('users', 'departments', 'positions', 'locations'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
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
            
            // [NEW] Fingerprint ID Validation (Unique ဖြစ်ရပါမယ်)
            'finger_print_id' => ['nullable', 'string', 'max:50', 'unique:'.User::class],
            
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            
            // [NEW] Location Validation
            'location' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $request->employee_id,
            'finger_print_id' => $request->finger_print_id, // [NEW] Saving Fingerprint ID
            'phone' => $request->phone,
            'location' => $request->location, // [NEW] Saving Location
            'role' => $request->role ?? 'user',
            'department' => $request->department,
            'position' => $request->position,
        ]);

        event(new Registered($user));

        return redirect()->route('users.create')->with('success', 'User created successfully!');
    }
}
