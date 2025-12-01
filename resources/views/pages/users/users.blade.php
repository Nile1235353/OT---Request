@extends('pages.layouts')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        {{-- ======================== --}}
        {{-- 1. LEFT COLUMN: CREATE FORM --}}
        {{-- ======================== --}}
        <div class="md:col-span-1">
            <div class="page-card sticky top-24"> {{-- Added sticky for better UX on long lists --}}
                <h3 class="text-xl font-bold mb-4 border-b pb-2">Create New User</h3>
                
                @if (session('success'))
                    <div class="mt-4 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700 mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-md bg-red-50 p-4 mb-4">
                        <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="space-y-4" method="POST" action="{{ route('users.store') }}">
                    @csrf
                    
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-800">User Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" value="{{ old('name') }}" required>
                    </div>

                    {{-- IDs --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="employee_id" class="block text-xs font-semibold text-gray-800">Employee ID</label>
                            <input type="text" id="employee_id" name="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" value="{{ old('employee_id') }}">
                        </div>
                        <div>
                            <label for="finger_print_id" class="block text-xs font-semibold text-gray-800">Fingerprint ID</label>
                            <input type="text" id="finger_print_id" name="finger_print_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" value="{{ old('finger_print_id') }}">
                        </div>
                    </div>

                    {{-- Contact --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-800">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" value="{{ old('email') }}" required>
                    </div>
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-gray-800">Phone</label>
                        <input type="text" id="phone" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" value="{{ old('phone') }}">
                    </div>

                    {{-- Location & Dept --}}
                    <div>
                        <label for="location" class="block text-xs font-semibold text-gray-800">Location</label>
                        <select id="location" name="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border">
                            <option value="" disabled selected>-- Select Location --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}" {{ old('location') == $location ? 'selected' : '' }}>{{ $location }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="department" class="block text-xs font-semibold text-gray-800">Department</label>
                        <select id="department" name="department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border">
                            <option value="" disabled selected>-- Select Department --</option>
                            @foreach($departments as $department)
                                <option value="{{ $department }}" {{ old('department') == $department ? 'selected' : '' }}>{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Position --}}
                    <div>
                        <label for="position" class="block text-xs font-semibold text-gray-800">Position</label>
                        <select id="position" name="position" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border">
                            <option value="" disabled selected>-- Select Position --</option>
                            @foreach($positions as $position)
                                <option value="{{ $position }}" {{ old('position') == $position ? 'selected' : '' }}>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Role --}}
                    <div>
                        <label for="role" class="block text-xs font-semibold text-gray-800">Role</label>
                        <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border">
                            @if(auth()->user()->role == 'Admin')
                                <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            @endif
                            <option value="Management" {{ old('role') == 'Management' ? 'selected' : 'selected' }}>Management</option>
                            <option value="HR" {{ old('role') == 'HR' ? 'selected' : '' }}>HR</option>
                            <option value="User" {{ old('role') == 'User' ? 'selected' : 'selected' }}>User</option>
                        </select>
                    </div>

                    {{-- Permissions --}}
                    <div class="mt-4 space-y-2">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Permissions</h4>
                        
                        <div class="flex items-center bg-gray-50 p-3 rounded border border-gray-200">
                            <input id="can_request_ot" name="can_request_ot" type="checkbox" value="1" 
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer"
                                {{ old('can_request_ot') ? 'checked' : '' }}>
                            <div class="ml-3 text-sm">
                                <label for="can_request_ot" class="font-medium text-gray-700 cursor-pointer">Allow OT Request</label>
                            </div>
                        </div>

                        <div class="flex items-center bg-gray-50 p-3 rounded border border-gray-200">
                            <input id="morning_ot" name="morning_ot" type="checkbox" value="1" 
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer"
                                {{ old('morning_ot') ? 'checked' : '' }}>
                            <div class="ml-3 text-sm">
                                <label for="morning_ot" class="font-medium text-gray-700 cursor-pointer">Morning OT</label>
                                <p class="text-xs text-gray-500">Enable morning OT eligibility.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-gray-800">Password <span class="text-red-500">*</span></label>
                            <input type="password" id="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" required>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-gray-800">Confirm PW <span class="text-red-500">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" required>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 font-bold text-sm shadow-sm transition-colors">Create User</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================== --}}
        {{-- 2. RIGHT COLUMN: USER LIST TABLE --}}
        {{-- ======================== --}}
        <div class="md:col-span-2">
            <div class="page-card">
                {{-- Header with Title and Search --}}
                <div class="flex flex-col sm:flex-row justify-between items-center mb-6 border-b pb-4 gap-4">
                    <h3 class="text-xl font-bold text-gray-800">User List</h3>
                    
                    {{-- [NEW] Search Form --}}
                    <form method="GET" action="{{ url()->current() }}" class="w-full sm:w-auto">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-64 shadow-sm" 
                                placeholder="Search by Name or ID...">
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-header">Name</th>
                                <th class="table-header">Emp ID</th>
                                <th class="table-header">FP ID</th>
                                <th class="table-header">Location</th>
                                <th class="table-header">Dept</th>
                                <th class="table-header">Position</th>
                                <th class="table-header">Role</th>
                                <th class="table-header text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="userTableBody">
                            @forelse ($users as $user)
                            <tr class="user-row hover:bg-gray-50/50 cursor-pointer transition-colors" 
                                data-user-id="{{ $user->id }}" 
                                data-user-name="{{ $user->name }}">
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{$user->name}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->employee_id}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 font-bold">{{$user->finger_print_id ?? '-'}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->location ?? '-'}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->department}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->position}}</td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role == 'Admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                        {{$user->role}}
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <button type="button" 
                                        class="edit-user-btn text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition duration-150 ease-in-out"
                                        data-user-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-phone="{{ $user->phone }}"
                                        data-employee-id="{{ $user->employee_id }}"
                                        data-finger-print-id="{{ $user->finger_print_id }}"
                                        data-location="{{ $user->location }}"
                                        data-department="{{ $user->department }}"
                                        data-position="{{ $user->position }}"
                                        data-role="{{ $user->role }}"
                                        data-can-request-ot="{{ $user->can_request_ot }}"
                                        data-morning-ot="{{ $user->morning_ot }}"> 
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    No users found matching your search.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- [NEW] Pagination Links --}}
                <div class="mt-4 px-4 py-3 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- ======================== --}}
    {{-- MODAL 1: VIEW OT HISTORY --}}
    {{-- ======================== --}}
    <div id="otModal" class="fixed inset-0 z-40 flex items-center justify-center hidden">
        <div id="otModalBackdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl z-50">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">OT History: <span id="modalUserName" class="text-indigo-600"></span></h3>
                    <button id="otModalClose" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between gap-4">
                        <select id="monthFilter" class="rounded-md border-gray-300 shadow-sm py-2 px-3"></select>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Total Hours:</div>
                            <div id="totalOTHours" class="text-2xl font-bold text-indigo-600">--</div>
                        </div>
                    </div>
                    <div class="mt-6 max-h-60 overflow-y-auto border border-gray-200 rounded-md">
                        <p id="otLoading" class="p-4 text-center text-gray-500">Loading...</p>
                        <div id="otListContent" class="hidden"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================== --}}
    {{-- MODAL 2: EDIT USER --}}
    {{-- ======================== --}}
    <div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" role="dialog" aria-modal="true">
        <div id="editUserBackdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-2xl w-full z-50 max-h-[90vh] overflow-y-auto border border-gray-100">
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Edit User Profile</h3>
                        <p class="text-sm text-gray-500 mt-1">Update employee information and permissions.</p>
                    </div>
                    <button type="button" id="closeEditModalTop" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-gray-100">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="px-8 py-6 space-y-8">
                    <div>
                        <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-4 border-b border-indigo-100 pb-2">Personal Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="edit_name" name="name" class="w-full px-4 py-2 rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition duration-200 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="edit_email" name="email" class="w-full px-4 py-2 rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition duration-200 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="text" id="edit_phone" name="phone" class="w-full px-4 py-2 rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition duration-200 outline-none">
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-4 border-b border-indigo-100 pb-2">Organization Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                                <input type="text" id="edit_employee_id" name="employee_id" class="w-full px-4 py-2 rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition duration-200 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fingerprint ID</label>
                                <input type="text" id="edit_finger_print_id" name="finger_print_id" class="w-full px-4 py-2 rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition duration-200 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <div class="relative">
                                    <select id="edit_department" name="department" class="w-full px-4 py-2 rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition duration-200 outline-none appearance-none">
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}">{{ $dept }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                <div class="relative">
                                    <select id="edit_position" name="position" class="w-full px-4 py-2 rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition duration-200 outline-none appearance-none">
                                        @foreach($positions as $pos)
                                            <option value="{{ $pos }}">{{ $pos }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <div class="relative">
                                    <select id="edit_location" name="location" class="w-full px-4 py-2 rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition duration-200 outline-none appearance-none">
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc }}">{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">System Role</label>
                                <div class="relative">
                                    <select id="edit_role" name="role" class="w-full px-4 py-2 rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition duration-200 outline-none appearance-none">
                                        @if(auth()->user()->role == 'Admin')
                                            <option value="Admin">Admin</option>
                                        @endif
                                        <option value="Management">Management</option>
                                        <option value="HR">HR</option>
                                        <option value="User">User</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 space-y-3">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100 pb-2">OT Permissions</h4>
                            <label class="flex items-start p-3 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex items-center h-5">
                                    <input id="edit_can_request_ot" name="can_request_ot" type="checkbox" value="1" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-bold text-gray-800 block">Allow Overtime Requests</span>
                                    <span class="text-gray-500 text-xs">Enable this if the user is authorized to submit OT claims.</span>
                                </div>
                            </label>
                            <label class="flex items-start p-3 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
                                <div class="flex items-center h-5">
                                    <input id="edit_morning_ot" name="morning_ot" type="checkbox" value="1" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-bold text-gray-800 block">Morning OT</span>
                                    <span class="text-gray-500 text-xs">Enable morning OT eligibility.</span>
                                </div>
                            </label>
                        </div>
                        @if(auth()->user()->role == 'Admin')
                            <div class="mt-6 flex justify-center">
                                <button type="button" id="openPasswordModalBtn" class="group flex items-center px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-full hover:bg-red-100 transition-all">
                                    <div class="p-1 bg-red-200 rounded-full mr-2 group-hover:bg-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    Change Password / Reset Access
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3 rounded-b-2xl">
                    <button type="button" id="closeEditModal" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 hover:text-gray-800 transition shadow-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 shadow-lg shadow-indigo-500/30 transition transform hover:-translate-y-0.5">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ======================== --}}
    {{-- MODAL 3: PASSWORD UPDATE --}}
    {{-- ======================== --}}
    @if(auth()->user()->role == 'Admin')
    <div id="passwordUpdateModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden">
        <div id="passwordBackdrop" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-md w-full z-[70] overflow-hidden">
            <div class="h-2 w-full bg-gradient-to-r from-red-500 to-orange-500"></div>
            <form id="passwordUpdateForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6 animate-pulse">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Security Update</h3>
                    <p class="text-sm text-gray-500 mt-2">
                        Set a new password for <span id="pwUserLabel" class="font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded"></span>.
                    </p>
                    <div class="mt-8 space-y-4 text-left">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1 ml-1">New Password</label>
                            <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition duration-200 outline-none placeholder-gray-400" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1 ml-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition duration-200 outline-none placeholder-gray-400" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between gap-3">
                    <button type="button" id="closePasswordModal" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 transition shadow-sm">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 shadow-lg shadow-red-500/30 transition transform hover:-translate-y-0.5">Update Password</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('User Management Script Loaded');

            // --- 1. OT History Modal Logic ---
            const otModal = document.getElementById('otModal');
            const otBackdrop = document.getElementById('otModalBackdrop');
            const otCloseBtn = document.getElementById('otModalClose');
            const userTableBody = document.getElementById('userTableBody');
            
            const modalUserName = document.getElementById('modalUserName');
            const monthFilter = document.getElementById('monthFilter');
            const totalOTHours = document.getElementById('totalOTHours');
            const otListContent = document.getElementById('otListContent');
            const otLoading = document.getElementById('otLoading');
            let currentUserId = null;

            function populateMonthFilter() {
                const today = new Date();
                monthFilter.innerHTML = '';
                for (let i = 0; i < 12; i++) {
                    const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
                    const monthValue = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
                    const monthName = date.toLocaleString('default', { month: 'long', year: 'numeric' });
                    const option = document.createElement('option');
                    option.value = monthValue;
                    option.textContent = monthName;
                    if (i === 0) option.selected = true;
                    monthFilter.appendChild(option); 
                }
            }
            populateMonthFilter();

            function openOTModal() { otModal.classList.remove('hidden'); }
            function closeOTModal() { otModal.classList.add('hidden'); }
            if(otCloseBtn) otCloseBtn.addEventListener('click', closeOTModal);
            if(otBackdrop) otBackdrop.addEventListener('click', closeOTModal);

            async function fetchOTData() {
                if (!currentUserId || !monthFilter.value) return;
                otLoading.classList.remove('hidden');
                otListContent.classList.add('hidden');
                otListContent.innerHTML = '';
                totalOTHours.textContent = '--';
                
                try {
                    const response = await fetch(`/users/${currentUserId}/ot?month=${monthFilter.value}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) throw new Error('Failed');
                    const data = await response.json();
                    
                    totalOTHours.textContent = (data.totalHours || 0) + ' hrs';
                    if (data.otList && data.otList.length > 0) {
                        let html = `<table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-4 py-2 text-xs text-gray-500">Date</th><th class="px-4 py-2 text-xs text-gray-500">Hours</th><th class="px-4 py-2 text-xs text-gray-500">Reason</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">`;
                        data.otList.forEach(r => {
                            html += `<tr><td class="px-4 py-2 text-sm">${r.date}</td><td class="px-4 py-2 text-sm font-bold">${r.hours}</td><td class="px-4 py-2 text-sm">${r.reason}</td></tr>`;
                        });
                        html += `</tbody></table>`;
                        otListContent.innerHTML = html;
                        otListContent.classList.remove('hidden');
                    } else {
                        otListContent.innerHTML = '<p class="p-4 text-center text-sm text-gray-500">No records found.</p>';
                        otListContent.classList.remove('hidden');
                    }
                } catch (e) { console.error(e); }
                otLoading.classList.add('hidden');
            }
            monthFilter.addEventListener('change', fetchOTData);

            // --- 2. Edit User Modal Logic ---
            const editModal = document.getElementById('editUserModal');
            const editBackdrop = document.getElementById('editUserBackdrop');
            const closeEditBtn = document.getElementById('closeEditModal');
            const closeEditBtnTop = document.getElementById('closeEditModalTop');
            const editForm = document.getElementById('editUserForm');
            const editButtons = document.querySelectorAll('.edit-user-btn');

            editButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); 
                    const uid = this.dataset.userId;
                    editForm.action = `/users/${uid}`; 
                    
                    document.getElementById('edit_name').value = this.dataset.name;
                    document.getElementById('edit_email').value = this.dataset.email;
                    document.getElementById('edit_phone').value = this.dataset.phone || '';
                    document.getElementById('edit_employee_id').value = this.dataset.employeeId || '';
                    document.getElementById('edit_finger_print_id').value = this.dataset.fingerPrintId || '';
                    
                    const deptSel = document.getElementById('edit_department'); if(deptSel) deptSel.value = this.dataset.department;
                    const posSel = document.getElementById('edit_position'); if(posSel) posSel.value = this.dataset.position;
                    const locSel = document.getElementById('edit_location'); if(locSel) locSel.value = this.dataset.location;
                    const roleSel = document.getElementById('edit_role'); if(roleSel) roleSel.value = this.dataset.role;
                    
                    document.getElementById('edit_can_request_ot').checked = (this.dataset.canRequestOt == "1");
                    document.getElementById('edit_morning_ot').checked = (this.dataset.morningOt == "1");

                    const pwBtn = document.getElementById('openPasswordModalBtn');
                    if(pwBtn) {
                        pwBtn.dataset.userId = uid;
                        pwBtn.dataset.userName = this.dataset.name;
                    }

                    editModal.classList.remove('hidden');
                });
            });

            function closeEdit() { editModal.classList.add('hidden'); }
            if(closeEditBtn) closeEditBtn.addEventListener('click', closeEdit);
            if(closeEditBtnTop) closeEditBtnTop.addEventListener('click', closeEdit);
            if(editBackdrop) editBackdrop.addEventListener('click', closeEdit);

            // --- 3. Password Update Modal ---
            const pwModal = document.getElementById('passwordUpdateModal');
            const pwBackdrop = document.getElementById('passwordBackdrop');
            const openPwBtn = document.getElementById('openPasswordModalBtn');
            const closePwBtn = document.getElementById('closePasswordModal');
            const pwForm = document.getElementById('passwordUpdateForm');

            if(openPwBtn) {
                openPwBtn.addEventListener('click', function() {
                    const uid = this.dataset.userId;
                    const uName = this.dataset.userName;
                    pwForm.action = `/users/${uid}/password`; 
                    document.getElementById('pwUserLabel').textContent = uName;
                    pwModal.classList.remove('hidden');
                });
            }
            function closePw() { pwModal.classList.add('hidden'); }
            if(closePwBtn) closePwBtn.addEventListener('click', closePw);
            if(pwBackdrop) pwBackdrop.addEventListener('click', closePw);

            // --- 4. Main Row Click ---
            userTableBody.addEventListener('click', function(e) {
                if(e.target.closest('.edit-user-btn')) return;
                const row = e.target.closest('.user-row');
                if (row) {
                    currentUserId = row.dataset.userId;
                    modalUserName.textContent = row.dataset.userName;
                    openOTModal();
                    fetchOTData();
                }
            });
        });
    </script>
@endsection