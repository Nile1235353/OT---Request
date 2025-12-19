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
                    <input type="time" id="start_time" name="start_time" value="{{ old('start_time', '17:30') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                    <input type="time" id="end_time" name="end_time" value="{{ old('end_time', '20:30') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                </div>
                <div>
                    <label for="total_hours_display" class="block text-sm font-medium text-gray-700">Total Hours (HH:MM)</label>
                    {{-- Display Only Field (User မြင်ရဖို့) --}}
                    <input type="text" id="total_hours_display" value="03:00" class="mt-1 block w-full rounded-lg border-gray-400 bg-gray-100 focus:border-indigo-500 py-2.5 px-4 font-mono font-bold text-indigo-700" readonly>
                    {{-- Hidden Field for Backend Validation (Decimal ပုံစံပို့ဖို့) --}}
                    <input type="hidden" id="total_hours" name="total_hours" value="3.00">
                    <p class="mt-1 text-xs text-gray-500 italic">Auto-calculated format.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                <div>
                    <label for="requirement_type" class="block text-sm font-medium text-gray-700">Requirement Type</label>
                    <select id="requirement_type" name="requirement_type" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                        <option value="" disabled selected>-- Select Type --</option>
                        <option value="Customer Requirement" {{ old('requirement_type') == 'Customer Requirement' ? 'selected' : '' }}>Customer Requirement</option>
                        <option value="RG Requirement" {{ old('requirement_type') == 'RG Requirement' ? 'selected' : '' }}>RG Requirement</option>
                    </select>
                </div>
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                    <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" placeholder="e.g. DHL, Coca Cola">
                </div>
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
                <div class="mb-3 p-3 bg-indigo-50 rounded-md border border-indigo-100">
                    <label for="department_filter" class="block text-xs font-semibold text-indigo-700 mb-1">Filter by Department</label>
                    <select id="department_filter" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="" disabled selected>-- Select a Department --</option>
                        <option value="{{ auth()->user()->department }}">{{ auth()->user()->department }} (My Dept)</option>
                        <option value="All">All Departments</option>
                        @foreach($departments as $dept)
                            @if($dept != auth()->user()->department)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <label for="team_members" class="block text-sm font-medium text-gray-700">Members</label>
                <select multiple id="team_members" name="team_members[]" placeholder="Search & Select members...">
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" data-name="{{ $employee->name }}" {{ (collect(old('team_members'))->contains($employee->id)) ? 'selected' : '' }}>
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
            <button type="submit" class="bg-indigo-600 text-white py-2 px-6 rounded-md hover:bg-indigo-700 shadow-md font-semibold transition-all">Submit Request</button>
        </div>
    </form>

    {{-- Recent Requests Section --}}
    <div class="mt-16 pt-8 border-t border-gray-200">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                My Recent Requests
            </h3>
        </div>

        {{-- Filter Form for Recent Requests --}}
        <form method="GET" action="{{ route('overtime.create') }}" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label for="filter_month" class="block text-xs font-bold text-gray-500 uppercase mb-1">Month</label>
                    <select name="filter_month" id="filter_month" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Months</option>
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ request('filter_month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="filter_year" class="block text-xs font-bold text-gray-500 uppercase mb-1">Year</label>
                    <select name="filter_year" id="filter_year" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Years</option>
                        @foreach(range(date('Y'), date('Y')-2) as $y)
                            <option value="{{ $y }}" {{ request('filter_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_request_id" class="block text-xs font-bold text-gray-500 uppercase mb-1">Request ID</label>
                    <input type="text" name="filter_request_id" value="{{ request('filter_request_id') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. OT-2025">
                </div>
                <div>
                    <label for="filter_customer" class="block text-xs font-bold text-gray-500 uppercase mb-1">Customer</label>
                    <input type="text" name="filter_customer" value="{{ request('filter_customer') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. DHL">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none h-[38px]">Filter</button>
                    <a href="{{ route('overtime.create') }}" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none h-[38px]">Clear</a>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th> 
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requirement</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($myRequests ?? [] as $request)
                        @php
                            // HH:MM format ပြောင်းရန်
                            $dec = $request->total_hours;
                            $h = floor($dec);
                            $m = round(($dec - $h) * 60);
                            $formattedHours = sprintf('%02d:%02d', $h, $m);
                        @endphp
                        <tr class="hover:bg-indigo-50 cursor-pointer transition-colors duration-200" 
                            onclick="openModal(this)"
                            data-json="{{ json_encode($request->load('assignTeams.user', 'supervisor')) }}"
                            data-hours-formatted="{{ $formattedHours }}">
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ \Carbon\Carbon::parse($request->ot_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                {{ $request->request_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $request->supervisor->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                {{ $request->customer_name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $request->requirement_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                                {{ $formattedHours }}
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
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 italic">
                                No recent requests found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- OT Detail Modal Structure --}}
<div id="otDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <div class="bg-indigo-600 px-4 py-3 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">OT Request Details</h3>
                <button type="button" onclick="closeModal()" class="text-indigo-200 hover:text-white focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div><p class="text-xs text-gray-500 uppercase font-bold">Request ID</p><p id="m_request_id" class="text-gray-900 font-medium">-</p></div>
                    <div><p class="text-xs text-gray-500 uppercase font-bold">Date</p><p id="m_ot_date" class="text-gray-900 font-medium">-</p></div>
                    <div><p class="text-xs text-gray-500 uppercase font-bold">Time</p><p class="text-gray-900 font-medium"><span id="m_start_time"></span> - <span id="m_end_time"></span> (<span id="m_total_hours"></span>)</p></div>
                    <div><p class="text-xs text-gray-500 uppercase font-bold">Status</p><span id="m_status" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">-</span></div>
                    <div><p class="text-xs text-gray-500 uppercase font-bold">Customer</p><p id="m_customer" class="text-gray-900">-</p></div>
                    <div><p class="text-xs text-gray-500 uppercase font-bold">Job Code</p><p id="m_job_code" class="text-gray-900">-</p></div>
                </div>
                <div class="mb-6"><p class="text-xs text-gray-500 uppercase font-bold mb-1">Reason</p><p id="m_reason" class="text-gray-700 bg-gray-50 p-3 rounded border border-gray-200">-</p></div>
                <div><h4 class="text-sm font-bold text-gray-800 mb-2 border-b pb-1">Team Members & Tasks</h4><div class="border rounded-md overflow-hidden"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employee</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Task</th></tr></thead><tbody id="m_team_tbody" class="bg-white divide-y divide-gray-200"></tbody></table></div></div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"><button type="button" onclick="closeModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Close</button></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
/**
 * HH:MM Format ဖြင့် အချိန်တွက်ချက်ပေးသည့် Function
 */
function calculateHours() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const displayInput = document.getElementById('total_hours_display');
    const hiddenInput = document.getElementById('total_hours');

    if (startTimeInput.value && endTimeInput.value) {
        const startParts = startTimeInput.value.split(':');
        const endParts = endTimeInput.value.split(':');
        
        let startMins = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
        let endMins = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);

        if (endMins <= startMins) {
            endMins += 1440; // Cross-day
        }

        const diffMinutes = endMins - startMins;
        const hours = Math.floor(diffMinutes / 60);
        const minutes = diffMinutes % 60;

        // ၁။ Display အတွက် HH:MM format
        const formattedHours = String(hours).padStart(2, '0');
        const formattedMinutes = String(minutes).padStart(2, '0');
        displayInput.value = `${formattedHours}:${formattedMinutes}`; 

        // ၂။ Backend အတွက် Decimal format (ဥပမာ - 3.50)
        const decimalValue = (diffMinutes / 60).toFixed(2);
        hiddenInput.value = decimalValue;
    } else {
        displayInput.value = '';
        hiddenInput.value = '';
    }
}

document.addEventListener("DOMContentLoaded", function () {
    calculateHours(); 
    document.getElementById('start_time').addEventListener('input', calculateHours);
    document.getElementById('end_time').addEventListener('input', calculateHours);
    
    // TomSelect Logic
    const jobsContainer = document.getElementById('jobs-container');
    const placeholderText = document.getElementById('placeholder-text');
    const deptFilter = document.getElementById('department_filter');
    const oldTasks = @json(old('tasks', []));

    const ts = new TomSelect('#team_members', {
        plugins: ['remove_button'],
        maxOptions: null,
        onItemAdd: function (value, item) {
            createTaskInput(value, item.textContent);
        },
        onItemRemove: function (value) {
            const div = document.getElementById('job-div-' + value);
            if (div) div.remove();
            if (ts.items.length === 0 && placeholderText) placeholderText.style.display = 'block';
        }
    });

    function createTaskInput(value, rawText) {
        if (placeholderText) placeholderText.style.display = 'none';
        if (!document.getElementById('job-div-' + value)) {
            const div = document.createElement('div');
            div.id = 'job-div-' + value;
            div.className = "bg-white p-3 rounded border border-gray-200 shadow-sm animate-fade-in-down";
            const oldValue = oldTasks[value] || '';
            div.innerHTML = `
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm font-bold text-indigo-700">${rawText.trim()}</label>
                    <button type="button" onclick="removeMember('${value}')" class="text-xs text-red-500">Remove</button>
                </div>
                <input type="text" name="tasks[${value}]" value="${oldValue}" class="mt-1 block w-full rounded-md border-gray-300 sm:text-sm py-2 px-3 border" placeholder="Assign task..." required>
            `;
            jobsContainer.appendChild(div);
        }
    }

    window.removeMember = function(value) { ts.removeItem(value); };

    // Dept Filter Logic
    deptFilter.addEventListener('change', function() {
        const selectedDept = this.value;
        if(!selectedDept) return;
        this.disabled = true;
        fetch(`/get-employees-by-dept?department=${encodeURIComponent(selectedDept)}`)
            .then(r => r.json())
            .then(data => {
                const selectedValues = ts.getValue();
                Object.keys(ts.options).forEach(v => { if (!selectedValues.includes(v)) ts.removeOption(v); });
                data.forEach(e => ts.addOption({ value: e.id, text: `${e.name} (${e.department})` }));
                ts.refreshOptions(false);
                this.disabled = false;
            })
            .catch(() => { this.disabled = false; });
    });
});

function openModal(element) {
    const data = JSON.parse(element.getAttribute('data-json'));
    const formattedHours = element.getAttribute('data-hours-formatted');

    document.getElementById('m_request_id').innerText = data.request_id || '-';
    document.getElementById('m_ot_date').innerText = data.ot_date || '-';
    document.getElementById('m_start_time').innerText = data.start_time || '-';
    document.getElementById('m_end_time').innerText = data.end_time || '-';
    document.getElementById('m_total_hours').innerText = formattedHours + ' hrs';
    document.getElementById('m_customer').innerText = data.customer_name || '-';
    document.getElementById('m_job_code').innerText = data.job_code || '-';
    document.getElementById('m_reason').innerText = data.reason || '-';
    
    const statusSpan = document.getElementById('m_status');
    statusSpan.innerText = data.status;
    statusSpan.className = "px-2 inline-flex text-xs leading-5 font-semibold rounded-full " + 
        (data.status === 'Approved' ? 'bg-green-100 text-green-800' : (data.status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'));

    const tbody = document.getElementById('m_team_tbody');
    tbody.innerHTML = '';
    if (data.assign_teams && data.assign_teams.length > 0) {
        data.assign_teams.forEach(item => {
            const row = `<tr><td class="px-4 py-2 text-sm text-gray-900"><div class="font-medium">${item.user ? item.user.name : 'Unknown'}</div></td><td class="px-4 py-2 text-sm text-gray-500">${item.task_description || '-'}</td></tr>`;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }
    document.getElementById('otDetailModal').classList.remove('hidden');
}

function closeModal() { document.getElementById('otDetailModal').classList.add('hidden'); }
</script>

<style>
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-down { animation: fadeInDown 0.3s ease-out; }
</style>
@endpush