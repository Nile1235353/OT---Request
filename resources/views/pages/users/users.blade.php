@extends('pages.layouts')

@section('content')
    {{-- Main Title From Image --}}
    <!-- <h2 class="text-3xl font-bold mb-8 text-gray-800">1. User Management Page Design</h2> -->

    {{-- Main Grid Container for two columns --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        {{-- Left Column: Create User Form (Updated Style) --}}
        <div class="md:col-span-1">
            <div class="page-card">
                <h3 class="text-xl font-bold mb-4 border-b pb-2">Create New User</h3>
                {{-- Display Success Message --}}
                @if (session('success'))
                    <div class="mt-4 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Display Validation Errors --}}
                @if ($errors->any())
                    <div class="mt-4 rounded-md bg-red-50 p-4">
                        <div class="font-medium text-red-700">Whoops! Something went wrong.</div>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- User Creation Form --}}
                <form class="space-y-5 mt-6" method="POST" action="{{ route('users.store') }}">
                    @csrf
                    {{-- Form Fields --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-800">User Name</label>
                        <input type="text" id="name" name="name"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400"
                            placeholder="e.g., U Ba" value="{{ old('name') }}">
                    </div>
                    <div>
                        <label for="employee_id" class="block text-sm font-semibold text-gray-800">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400"
                            placeholder="e.g., EMP001" value="{{ old('employee_id') }}">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-800">Email</label>
                        <input type="text" id="email" name="email"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400"
                            placeholder="e.g., example@gmail.com" value="{{ old('email') }}">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-800">Phone Number</label>
                        <input type="text" id="phone" name="phone"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400"
                            placeholder="e.g., 09xxxxxxxxx" value="{{ old('phone') }}">
                    </div>

                    {{-- UPDATED DEPARTMENT FIELD --}}
                    <div>
                        <label for="department" class="block text-sm font-semibold text-gray-800">Department</label>
                        <select id="department" name="department" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4">
                            <option value="" disabled selected>-- Select a Department --</option>
                            @foreach($departments as $department)
                                <option value="{{ $department }}" {{ old('department') == $department ? 'selected' : '' }}>{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- UPDATED POSITION FIELD --}}
                    <div>
                        <label for="position" class="block text-sm font-semibold text-gray-800">Position</label>
                        <select id="position" name="position" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4">
                            <option value="" disabled selected>-- Select a Position --</option>
                            @foreach($positions as $position)
                                <option value="{{ $position }}" {{ old('position') == $position ? 'selected' : '' }}>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-800">Role</label>
                        <select id="role" name="role"
                                class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4">
                            {{-- Login ဝင်ထားတဲ့ user ရဲ့ role က 'Admin' ဖြစ်မှ ဒီ option ကိုပြပါမယ် --}}
                            @if(auth()->user()->role == 'Admin')
                                <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            @endif
                            <option value="HR" {{ old('role') == 'HR' ? 'selected' : '' }}>HR</option>
                            <option value="User" {{ old('role') == 'User' ? 'selected' : 'selected' }}>User</option>
                        </select>
                    </div>
                    {{-- Password Fields --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-800">Password</label>
                        <input type="password" id="password" name="password"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4"
                            required>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-800">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4"
                            required>
                    </div>
                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-semibold">Create User</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Column: User List Table (MODIFIED) --}}
        <div class="md:col-span-2">
            <div class="page-card">
                <h3 class="text-xl font-bold mb-4 border-b pb-2">User List</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-header">Name</th>
                                <th class="table-header">Employee ID</th>
                                <th class="table-header">Email</th>
                                <th class="table-header">Phone</th>
                                <th class="table-header">Department</th>
                                <th class="table-header">Position</th>
                                <th class="table-header">Role</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="userTableBody">
                            @forelse ($users as $user)
                            {{-- Added user-row class and data attributes for JS --}}
                            <tr class="user-row hover:bg-gray-50/50 cursor-pointer" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{$user->name}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->employee_id}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->email}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->phone}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->department}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->position}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->role}}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- NEW: Overtime Modal Box --}}
    {{-- ================================================================= --}}
    <div id="otModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Modal Backdrop --}}
        <div id="otModalBackdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        {{-- Modal Content --}}
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="w-full text-center sm:mt-0 sm:text-left">
                        {{-- Modal Header --}}
                        <div class="flex justify-between items-center pb-3 border-b">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                Overtime Details for: <span id="modalUserName" class="text-indigo-600"></span>
                            </h3>
                            <button id="otModalClose" type="button" class="text-gray-400 hover:text-gray-600">
                                {{-- Heroicon: x-mark --}}
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        {{-- Modal Body --}}
                        <div class="mt-4">
                            {{-- Filter Section --}}
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-shrink-0">
                                    <label for="monthFilter" class="block text-sm font-medium text-gray-700">Select Month:</label>
                                    <select id="monthFilter" name="month" class="mt-1 block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                                        {{-- Options will be populated by JavaScript --}}
                                    </select>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Total OT for selected month:</div>
                                    <div id="totalOTHours" class="text-2xl font-bold text-indigo-600">-- hours</div>
                                </div>
                            </div>

                            {{-- OT List Container --}}
                            <div class="mt-6">
                                <h4 class="text-md font-semibold text-gray-800 mb-2">OT Records</h4>
                                <div id="otListContainer" class="max-h-60 overflow-y-auto rounded-md border border-gray-200">
                                    {{-- Loading/Empty/Data state will be populated by JS --}}
                                    <p id="otLoading" class="p-4 text-center text-gray-500">Loading...</p>
                                    <div id="otListContent" class="hidden">
                                        {{-- This will be replaced by a table --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button id="otModalCloseButton" type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    Close
                </button>
            </div>
        </div>
    </div>


    {{-- ================================================================= --}}
    {{-- NEW: JavaScript for Modal Logic --}}
    {{-- ================================================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Elements
            const modal = document.getElementById('otModal');
            const backdrop = document.getElementById('otModalBackdrop');
            const closeModalButtons = [
                document.getElementById('otModalClose'),
                document.getElementById('otModalCloseButton')
            ];
            const userTableBody = document.getElementById('userTableBody');
            
            // Modal Content Elements
            const modalUserName = document.getElementById('modalUserName');
            const monthFilter = document.getElementById('monthFilter');
            const totalOTHours = document.getElementById('totalOTHours');
            const otListContainer = document.getElementById('otListContainer');
            const otLoading = document.getElementById('otLoading');
            const otListContent = document.getElementById('otListContent');

            let currentUserId = null;

            // --- 1. Populate Month Filter (FIXED) ---
            function populateMonthFilter() {
                const today = new Date();
                monthFilter.innerHTML = ''; // Clear existing options

                for (let i = 0; i < 12; i++) {
                const date = new Date(today.getFullYear(), today.getMonth() - i, 1);

                    const year = date.getFullYear();
                    // .getMonth() က 0-indexed (Jan=0, Oct=9) ဖြစ်လို့ +1 ပေါင်းပြီး '0' ဖြင့် ဖြည့်ပါ
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const monthValue = `${year}-${month}`; // "YYYY-MM"
                    // --- ✅ END FIX ---

                    const monthName = date.toLocaleString('default', { month: 'long', year: 'numeric' });
                    
                    const option = document.createElement('option');
                    option.value = monthValue;
                    option.textContent = monthName;
                    
                    // Select the most recent full month by default (optional, but good)
                    // This logic makes 'October 2025' selected if today is 'November 2025'
                    if (i === 1) { 
                         // option.selected = true; // You can uncomment this if you want
                    }

                    // Select the current month by default
                    if (i === 0) {
                        option.selected = true;
                    }

                    monthFilter.appendChild(option); 
                }
            }

            // --- 2. Modal Open/Close Logic ---
            function openModal() {
                modal.classList.remove('hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
                currentUserId = null;
                // Reset modal state
                totalOTHours.textContent = '-- hours';
                otListContent.innerHTML = '';
                otListContent.classList.add('hidden');
                otLoading.textContent = 'Loading...';
                otLoading.classList.remove('hidden');
            }

            // Attach listeners to close buttons
            closeModalButtons.forEach(button => button.addEventListener('click', closeModal));
            backdrop.addEventListener('click', closeModal);

            // Attach listener to user table rows
            userTableBody.addEventListener('click', function(e) {
                const row = e.target.closest('.user-row');
                if (row) {
                    currentUserId = row.dataset.userId;
                    const userName = row.dataset.userName;
                    
                    modalUserName.textContent = userName;
                    openModal();
                    
                    // Fetch data for the default selected month
                    fetchOTData();
                }
            });

            // --- 3. Fetch OT Data ---
            async function fetchOTData() {
                if (!currentUserId) return;

                const selectedMonth = monthFilter.value;
                if (!selectedMonth) return;

                // Set loading state
                otLoading.classList.remove('hidden');
                otLoading.textContent = 'Loading...';
                otListContent.classList.add('hidden');
                otListContent.innerHTML = '';
                totalOTHours.textContent = '-- hours';

                try {
                    // IMPORTANT: This URL must match a route in your routes/web.php
                    // e.g., Route::get('/users/{user}/ot', [UserController::class, 'getOvertimeData']);
                    const response = await fetch(`/users/${currentUserId}/ot?month=${selectedMonth}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest', // Important for Laravel to know it's an AJAX request
                        }
                    });

                    if (!response.ok) {
                        // Get more details from the server's error response if possible
                        let serverError = await response.text();
                        try {
                            // Try to parse as JSON for Laravel's default error response
                            const errorJson = JSON.parse(serverError);
                            serverError = errorJson.message || serverError;
                        } catch(e) {
                            // It wasn't JSON, just use the text
                        }
                        
                        console.error('Server responded with error:', response.status, serverError);
                        // Throw a more specific error based on status
                        throw new Error(`Server error: ${response.status}.`);
                    }

                    const data = await response.json();

                    // Update Total Hours
                    totalOTHours.textContent = (data.totalHours || 0) + ' hours';

                    // Update OT List
                    if (data.otList && data.otList.length > 0) {
                        otListContent.innerHTML = createOTTable(data.otList);
                        otLoading.classList.add('hidden');
                        otListContent.classList.remove('hidden');
                    } else {
                        otLoading.textContent = 'No overtime records found for this month.';
                    }

                } catch (error) {
                    // Log the full error to the console for debugging
                    console.error('Error fetching OT data:', error); 
                    
                    // Display a more helpful error message in the modal
                    let errorMessage = 'Failed to load data. Please try again.';
                    if (error.message.includes('404')) {
                        errorMessage = 'Error: Not Found (404). Please check the route in routes/web.php.';
                    } else if (error.message.includes('500')) {
                        errorMessage = 'Error: Server Error (500). Please check the getOvertimeData method in your controller.';
                    } else if (error.message.includes('NetworkError')) {
                        // This catches network failures
                        errorMessage = 'A network error occurred. Please check your connection.';
                    } else {
                         // A more general message
                        errorMessage = `Error: ${error.message}. Check console for details.`;
                    }
                    
                    otLoading.textContent = errorMessage;
                    totalOTHours.textContent = 'Error';
                }
            }

            // --- 4. Helper to build OT list table ---
            function createOTTable(records) {
                let tableHtml = `
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason/Description</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                `;
                
                // IMPORTANT: Adjust 'date', 'hours', and 'reason' to match your database columns
                for (const record of records) {
                    tableHtml += `
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">${record.date || 'N/A'}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">${record.hours || 'N/A'}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${record.reason || 'N/A'}</td>
                        </tr>
                    `;
                }

                tableHtml += `</tbody></table>`;
                return tableHtml;
            }

            // --- 5. Initial Setup ---
            populateMonthFilter();
            
            // Add event listener for month filter changes
            monthFilter.addEventListener('change', fetchOTData);
        });
    </script>
@endsection