@extends('pages.layouts')

@section('content')

<div class="page-card max-w-4xl mx-auto">
    <h3 class="text-3xl font-bold mb-8 text-gray-800">New Overtime Request</h3>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm leading-5 font-medium text-red-800">မှားယွင်းမှုများ ရှိနေပါသည်။</h3>
                    <div class="mt-2 text-sm leading-5 text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('overtime.store') }}" method="POST" class="space-y-8">
        @csrf
        
        {{-- Section 1: Supervisor and Date --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
            <div>
                <label class="block text-sm font-medium text-gray-700">Requesting Supervisor</label>
                <div class="mt-1 p-2 border border-gray-200 bg-gray-50 rounded-md">
                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                </div>
            </div>
            <div>
                <label for="ot_date" class="block text-sm font-medium text-gray-700">OT Date</label>
                <input type="date" id="ot_date" name="ot_date" value="{{ old('ot_date', now()->format('Y-m-d')) }}" 
                       class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400 @error('ot_date') border-red-500 @enderror">
                @error('ot_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Section 2: Time Inputs and Info --}}
        <div class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                    <input type="time" id="start_time" name="start_time" value="{{ old('start_time', '18:00') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                    <input type="time" id="end_time" name="end_time" value="{{ old('end_time', '22:00') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                </div>
                <div>
                    <label for="total_hours" class="block text-sm font-medium text-gray-700">Total Hours</label>
                    <input type="number" id="total_hours" name="total_hours" step="0.5" value="{{ old('total_hours', '4.0') }}" placeholder="4.0" class="mt-1 block w-full rounded-lg border-gray-400 bg-gray-100 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" readonly required>
                    <p class="mt-1 text-xs text-gray-500">Auto-calculated.</p>
                </div>
            </div>

            {{-- Grid for Requirement Type, Customer Name, Job Code --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                {{-- Requirement Type --}}
                <div>
                    <label for="requirement_type" class="block text-sm font-medium text-gray-700">Requirement Type</label>
                    <select id="requirement_type" name="requirement_type" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                        <option value="" disabled selected>-- Select Type --</option>
                        <option value="Customer Requirement" {{ old('requirement_type') == 'Customer Requirement' ? 'selected' : '' }}>Customer Requirement</option>
                        <option value="RG Requirement" {{ old('requirement_type') == 'RG Requirement' ? 'selected' : '' }}>RG Requirement</option>
                    </select>
                </div>

                {{-- Customer Name --}}
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                    <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" placeholder="e.g. DHL, Coca Cola">
                </div>

                {{-- Job Code --}}
                <div>
                    <label for="job_code" class="block text-sm font-medium text-gray-700">Job Code <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                    <input type="text" id="job_code" name="job_code" value="{{ old('job_code') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" placeholder="e.g. JC-2025-001">
                </div>
            </div>

            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700">Reason for OT</label>
                <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" placeholder="Describe the reason...">{{ old('reason') }}</textarea>
            </div>
        </div>

        {{-- Section 3: Team Members and Jobs Assignment --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8 pt-4 border-t border-gray-200">
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-2">Select Team Members</label>
                
                {{-- Department Filter --}}
                <div class="mb-3 p-3 bg-indigo-50 rounded-md border border-indigo-100">
                    <label for="department_filter" class="block text-xs font-semibold text-indigo-700 mb-1">Filter by Department</label>
                    <select id="department_filter" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="" disabled selected>-- Select a Department --</option>
                        {{-- Logged in User's Dept --}}
                        <option value="{{ auth()->user()->department }}">{{ auth()->user()->department }} (My Dept)</option>
                        <option value="All">All Departments</option>
                        {{-- Other Departments --}}
                        @foreach($departments as $dept)
                            @if($dept != auth()->user()->department)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endif
                        @endforeach
                    </select>
                    <p class="text-xs text-indigo-500 mt-1">Select a department to load its employees.</p>
                </div>

                {{-- Team Members Select (TomSelect) --}}
                <label for="team_members" class="block text-sm font-medium text-gray-700">Members</label>
                <select multiple id="team_members" name="team_members[]" placeholder="Search & Select members...">
                    {{-- Default: Show employees from logged in user's department --}}
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" data-name="{{ $employee->name }}" {{ (collect(old('team_members'))->contains($employee->id)) ? 'selected' : '' }}>
                            {{-- Name (Department) --}}
                            {{ $employee->name }} ({{$employee->department}})
                        </option>
                    @endforeach
                </select>
                @error('team_members')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-2">Assign Tasks</label>
                 <div id="jobs-container" class="mt-1 space-y-3 max-h-[400px] overflow-y-auto border border-gray-200 rounded-md p-3 bg-gray-50 min-h-[200px]">
                    <p id="placeholder-text" class="text-sm text-gray-500 italic p-2 text-center">Selected members will appear here for task assignment.</p>
                 </div>
                 @error('tasks')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                 @enderror
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-indigo-600 text-white py-2 px-6 rounded-md hover:bg-indigo-700 shadow-md font-semibold">Submit Request</button>
        </div>
    </form>

    {{-- [NEW] My Recent Requests Section --}}
    <div class="mt-16 pt-8 border-t border-gray-200">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                My Recent Requests
            </h3>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('overtime.create') }}" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                {{-- Month Filter --}}
                <div>
                    <label for="filter_month" class="block text-xs font-bold text-gray-500 uppercase mb-1">Month</label>
                    <select name="filter_month" id="filter_month" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Months</option>
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ request('filter_month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Year Filter --}}
                <div>
                    <label for="filter_year" class="block text-xs font-bold text-gray-500 uppercase mb-1">Year</label>
                    <select name="filter_year" id="filter_year" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Years</option>
                        @foreach(range(date('Y'), date('Y')-2) as $y)
                            <option value="{{ $y }}" {{ request('filter_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Request ID Filter --}}
                <div>
                    <label for="filter_request_id" class="block text-xs font-bold text-gray-500 uppercase mb-1">Request ID</label>
                    <input type="text" name="filter_request_id" value="{{ request('filter_request_id') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. OT-2025">
                </div>

                {{-- Customer Filter --}}
                <div>
                    <label for="filter_customer" class="block text-xs font-bold text-gray-500 uppercase mb-1">Customer</label>
                    <input type="text" name="filter_customer" value="{{ request('filter_customer') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. DHL">
                </div>

                {{-- Filter Actions --}}
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        Filter
                    </button>
                    <a href="{{ route('overtime.create') }}" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                        Clear
                    </a>
                </div>
            </div>
        </form>
        
        {{-- Table --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                        {{-- [NEW] Requester Column --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th> 
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requirement</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($myRequests ?? [] as $request)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ \Carbon\Carbon::parse($request->ot_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                {{ $request->request_id }}
                            </td>
                            {{-- [NEW] Requester Data --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $request->supervisor->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                {{ $request->customer_name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $request->requirement_type }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                {{ $request->reason }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                                {{ $request->total_hours }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($request->status === 'Approved') bg-green-100 text-green-800 
                                    @elseif($request->status === 'Rejected') bg-red-100 text-red-800 
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500 italic">
                                No recent requests found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
// --- Time Calculation Logic ---
function calculateHours() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const totalHoursInput = document.getElementById('total_hours');

    if (startTimeInput && endTimeInput && totalHoursInput && startTimeInput.value && endTimeInput.value) {
        const dateString = '2000/01/01 '; 
        const startValue = startTimeInput.value;
        const endValue = endTimeInput.value;
        const startTime = new Date(dateString + startValue);
        let endTime = new Date(dateString + endValue);

        if (endTime.getTime() <= startTime.getTime()) {
            endTime.setDate(endTime.getDate() + 1);
        }

        const diffMilliseconds = endTime - startTime;
        const diffHours = diffMilliseconds / (1000 * 60 * 60);
        const roundedHours = Math.round(diffHours * 2) / 2;

        if (roundedHours > 0) {
            totalHoursInput.value = roundedHours.toFixed(1); 
        } else {
            totalHoursInput.value = ''; 
        }
    } else {
        totalHoursInput.value = '';
    }
}

document.addEventListener("DOMContentLoaded", function () {
    calculateHours(); 
    document.getElementById('start_time').addEventListener('input', calculateHours);
    document.getElementById('end_time').addEventListener('input', calculateHours);
    
    // --- TomSelect & Department Filter Logic ---
    const jobsContainer = document.getElementById('jobs-container');
    const placeholderText = document.getElementById('placeholder-text');
    const deptFilter = document.getElementById('department_filter');
    const oldTasks = @json(old('tasks', []));

    // Initialize TomSelect
    const ts = new TomSelect('#team_members', {
        plugins: ['remove_button'],
        maxOptions: null, // No limit
        closeAfterSelect: true,
        hideSelected: true,
        onItemAdd: function (value, item) {
            // item.textContent will be "Name (Department)"
            createTaskInput(value, item.textContent);
        },
        onItemRemove: function (value) {
            removeTaskInput(value);
        }
    });

    // Function to create Task Input
    function createTaskInput(value, rawText) {
        // [UPDATED] Use full text "Name (Department)" for Label
        const displayText = rawText.trim(); 
        
        if (placeholderText) placeholderText.style.display = 'none';

        if (!document.getElementById('job-div-' + value)) {
            const jobInputDiv = document.createElement('div');
            jobInputDiv.id = 'job-div-' + value;
            jobInputDiv.className = "bg-white p-3 rounded border border-gray-200 shadow-sm animate-fade-in-down";
            const oldValue = oldTasks[value] || '';

            jobInputDiv.innerHTML = `
                <div class="flex justify-between items-center mb-1">
                    <label for="job_${value}" class="block text-sm font-bold text-indigo-700">${displayText}</label>
                    <button type="button" onclick="removeMember('${value}')" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                </div>
                <input type="text" id="job_${value}" name="tasks[${value}]" 
                       value="${oldValue}"
                       class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 px-3 border" 
                       placeholder="Assign task..." required>
            `;
            jobsContainer.appendChild(jobInputDiv);
        }
    }

    // Function to remove Task Input
    function removeTaskInput(value) {
        const jobInputDiv = document.getElementById('job-div-' + value);
        if (jobInputDiv) jobInputDiv.remove();
        
        // Show placeholder if empty
        if (ts.items.length === 0 && placeholderText) {
            placeholderText.style.display = 'block';
        }
    }

    // Global function for Remove link in task box
    window.removeMember = function(value) {
        ts.removeItem(value);
    };

    // --- Dynamic Department Loading (SMART FILTER) ---
    deptFilter.addEventListener('change', function() {
        const selectedDept = this.value;
        if(!selectedDept) return;

        // Disable dropdown while loading
        this.disabled = true;
        const originalText = this.options[this.selectedIndex].text;
        this.options[this.selectedIndex].text = 'Loading...';

        fetch(`/get-employees-by-dept?department=${encodeURIComponent(selectedDept)}`)
            .then(response => response.json())
            .then(data => {
                
                // 1. Get currently selected items (to preserve them)
                const selectedValues = ts.getValue();

                // 2. Remove options that are NOT selected
                Object.keys(ts.options).forEach(optionValue => {
                    if (!selectedValues.includes(optionValue)) {
                        ts.removeOption(optionValue);
                    }
                });

                // 3. Add new options from the selected department
                data.forEach(employee => {
                    ts.addOption({
                        value: employee.id,
                        // [UPDATED] Name (Department)
                        text: `${employee.name} (${employee.department})`
                    });
                });

                // Refresh options to reflect changes
                ts.refreshOptions(false); 
                
                // Reset Dropdown UI
                this.disabled = false;
                this.options[this.selectedIndex].text = originalText;
            })
            .catch(error => {
                console.error('Error:', error);
                this.disabled = false;
                this.options[this.selectedIndex].text = originalText;
                alert('Failed to load employees.');
            });
    });

    // Handle Old Inputs
    const selectedOptions = document.getElementById('team_members').options;
    for (let i = 0; i < selectedOptions.length; i++) {
        if (selectedOptions[i].selected) {
            createTaskInput(selectedOptions[i].value, selectedOptions[i].text);
        }
    }
});
</script>

<style>
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.3s ease-out;
    }
</style>
@endpush