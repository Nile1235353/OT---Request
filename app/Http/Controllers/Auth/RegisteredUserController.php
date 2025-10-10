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
        // Login ဝင်ထားတဲ့ user ရဲ့ role က 'Admin' or 'HR' မဟုတ်ဘူးဆိုရင်
        if ( !in_array(Auth::user()->role, ['Admin', 'HR']) ) {
            // Error message နဲ့အတူ အနောက်ကိုပြန်သွားပါ
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Role Authorization Check ---

        // return view('pages.users.users');
        $users = User::all();

        // Dropdown မှာထည့်မယ့် data list အသစ်တွေ
        $departments = ['Warehouse', 'BD', 'SCS', 'Data Center', 'ICD', 'CCA', 'IT', 'IT & Process', 'M&E', 'M&R', 'QEHS', 'HR', 'Corporate','Truck','Yard & Rail', 'Process', 'Finance'];
        $positions = ['Manager','Assistant Supervisor', 'Supervisor', 'Staff'];

        return view('pages.users.users', compact('users', 'departments', 'positions'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // --- START: Role Authorization Check ---
        // Login ဝင်ထားတဲ့ user ရဲ့ role က 'Admin' or 'HR' မဟုတ်ဘူးဆိုရင်
        if ( !in_array(Auth::user()->role, ['Admin', 'HR']) ) {
            // Error message နဲ့အတူ အနောက်ကိုပြန်သွားပါ
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
        // --- END: Role Authorization Check ---

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'employee_id' => ['nullable', 'string', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
        ]);

        // dd($request->all());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $request->employee_id,
            'phone' => $request->phone,
            'role' => $request->role ?? 'user',
            'department' => $request->department,
            'position' => $request->position,
        ]);

        event(new Registered($user));

        // Auth::login($user);

        // return redirect(route('users.create', absolute: false));

        return redirect()->route('users.create')->with('success', 'User created successfully!');
    }
}
