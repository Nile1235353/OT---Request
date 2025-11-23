@extends('pages.layouts')

@section('content')
    {{-- Main Grid Container for two columns --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        {{-- Left Column: Create User Form --}}
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
                <form class="space-y-4 mt-4" method="POST" action="{{ route('users.store') }}">
                    @csrf
                    
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-800">User Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" placeholder="e.g., U Ba" value="{{ old('name') }}" required>
                    </div>

                    {{-- Employee ID & Fingerprint ID Row --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="employee_id" class="block text-xs font-semibold text-gray-800">Employee ID</label>
                            <input type="text" id="employee_id" name="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" placeholder="EMP001" value="{{ old('employee_id') }}">
                        </div>
                        <div>
                            <label for="finger_print_id" class="block text-xs font-semibold text-gray-800">Fingerprint ID</label>
                            <input type="text" id="finger_print_id" name="finger_print_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" placeholder="e.g. 57" value="{{ old('finger_print_id') }}">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-800">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" placeholder="example@gmail.com" value="{{ old('email') }}" required>
                    </div>
                    
                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-gray-800">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" placeholder="09xxxxxxxxx" value="{{ old('phone') }}">
                    </div>

                    {{-- Location (New) --}}
                    <div>
                        <label for="location" class="block text-xs font-semibold text-gray-800">Location</label>
                        <select id="location" name="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border">
                            <option value="" disabled selected>-- Select Location --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}" {{ old('location') == $location ? 'selected' : '' }}>{{ $location }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department --}}
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
                            <option value="HR" {{ old('role') == 'HR' ? 'selected' : '' }}>HR</option>
                            <option value="User" {{ old('role') == 'User' ? 'selected' : 'selected' }}>User</option>
                        </select>
                    </div>

                    {{-- Password Fields --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-gray-800">Password <span class="text-red-500">*</span></label>
                            <input type="password" id="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" required>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-gray-800">Confirm PW <span class="text-red-500">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" required>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-bold text-sm">Create User</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Column: User List Table --}}
        <div class="md:col-span-2">
            <div class="page-card">
                <h3 class="text-xl font-bold mb-4 border-b pb-2">User List</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-header">Name</th>
                                <th class="table-header">Emp ID</th>
                                <th class="table-header">FP ID</th> 
                                <th class="table-header">Location</th>
                                <th class="table-header">Department</th>
                                <th class="table-header">Position</th>
                                <th class="table-header">Role</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="userTableBody">
                            @forelse ($users as $user)
                            {{-- IMPORTANT: user-row class and data attributes MUST be here --}}
                            <tr class="user-row hover:bg-gray-50/50 cursor-pointer" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{$user->name}}</td>
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
    {{-- MODAL BOX: Overtime Details --}}
    {{-- ================================================================= --}}
    <div id="otModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div id="otModalBackdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="w-full text-center sm:mt-0 sm:text-left">
                        <div class="flex justify-between items-center pb-3 border-b">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                Overtime Details for: <span id="modalUserName" class="text-indigo-600"></span>
                            </h3>
                            <button id="otModalClose" type="button" class="text-gray-400 hover:text-gray-600">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mt-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-shrink-0">
                                    <label for="monthFilter" class="block text-sm font-medium text-gray-700">Select Month:</label>
                                    <select id="monthFilter" name="month" class="mt-1 block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3">
                                        {{-- Options populated by JS --}}
                                    </select>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Total OT for selected month:</div>
                                    <div id="totalOTHours" class="text-2xl font-bold text-indigo-600">-- hours</div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <h4 class="text-md font-semibold text-gray-800 mb-2">OT Records</h4>
                                <div id="otListContainer" class="max-h-60 overflow-y-auto rounded-md border border-gray-200">
                                    <p id="otLoading" class="p-4 text-center text-gray-500">Loading...</p>
                                    <div id="otListContent" class="hidden">
                                        {{-- Table will be inserted here --}}
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

    {{-- MOVED SCRIPT HERE TO ENSURE IT LOADS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('User Management Script Loaded');

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
            const otListContent = document.getElementById('otListContent');
            const otLoading = document.getElementById('otLoading');

            let currentUserId = null;

            // 1. Populate Month Filter
            function populateMonthFilter() {
                const today = new Date();
                monthFilter.innerHTML = '';

                for (let i = 0; i < 12; i++) {
                    const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
                    const year = date.getFullYear();
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const monthValue = `${year}-${month}`;
                    const monthName = date.toLocaleString('default', { month: 'long', year: 'numeric' });
                    
                    const option = document.createElement('option');
                    option.value = monthValue;
                    option.textContent = monthName;
                    
                    if (i === 0) option.selected = true;
                    monthFilter.appendChild(option); 
                }
            }

            // 2. Modal Logic
            function openModal() {
                console.log('Opening Modal...');
                modal.classList.remove('hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
                currentUserId = null;
                totalOTHours.textContent = '-- hours';
                otListContent.innerHTML = '';
                otListContent.classList.add('hidden');
                otLoading.classList.remove('hidden');
            }

            closeModalButtons.forEach(btn => {
                if(btn) btn.addEventListener('click', closeModal);
            });
            if(backdrop) backdrop.addEventListener('click', closeModal);

            // 3. Row Click Listener
            if (userTableBody) {
                userTableBody.addEventListener('click', function(e) {
                    // Find the closest row
                    const row = e.target.closest('.user-row');
                    
                    if (row) {
                        console.log('Row Clicked:', row.dataset.userName);
                        currentUserId = row.dataset.userId;
                        modalUserName.textContent = row.dataset.userName;
                        
                        openModal();
                        fetchOTData();
                    }
                });
            } else {
                console.error('Table Body Not Found!');
            }

            // 4. Fetch Data
            async function fetchOTData() {
                if (!currentUserId || !monthFilter.value) return;

                otLoading.classList.remove('hidden');
                otLoading.textContent = 'Loading...';
                otListContent.classList.add('hidden');
                otListContent.innerHTML = '';
                totalOTHours.textContent = '-- hours';

                try {
                    console.log(`Fetching OT data for user ${currentUserId} month ${monthFilter.value}`);
                    const response = await fetch(`/users/${currentUserId}/ot?month=${monthFilter.value}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (!response.ok) throw new Error('Failed to fetch data');

                    const data = await response.json();
                    console.log('Data received:', data);
                    
                    totalOTHours.textContent = (data.totalHours || 0) + ' hours';

                    if (data.otList && data.otList.length > 0) {
                        otListContent.innerHTML = createOTTable(data.otList);
                        otLoading.classList.add('hidden');
                        otListContent.classList.remove('hidden');
                    } else {
                        otLoading.textContent = 'No overtime records found for this month.';
                    }

                } catch (error) {
                    console.error('Fetch Error:', error);
                    otLoading.textContent = 'Error loading data.';
                }
            }

            // 5. Build Table Helper
            function createOTTable(records) {
                let html = `
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">`;
                
                records.forEach(record => {
                    html += `
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">${record.date}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 font-bold">${record.hours}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">${record.reason}</td>
                        </tr>`;
                });

                html += `</tbody></table>`;
                return html;
            }

            // Init
            populateMonthFilter();
            if(monthFilter) monthFilter.addEventListener('change', fetchOTData);
        });
    </script>
@endsection