@extends('pages.layouts')

@section('content')

<div class="page-card max-w-4xl mx-auto">
    <h3 class="text-3xl font-bold mb-8 text-gray-800">New Overtime Request</h3>

    {{-- ========================================== --}}
    {{-- ERROR ALERT BOX (Validation Errors) --}}
    {{-- ========================================== --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    {{-- Error Icon --}}
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm leading-5 font-medium text-red-800">
                        မှားယွင်းမှုများ ရှိနေပါသည်။
                    </h3>
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

    {{-- Permission Error Alert --}}
    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Success Alert --}}
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
                
                {{-- Individual Field Error --}}
                @error('ot_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Section 2: Time Inputs, Calculated Hours, and Requirement Type --}}
        <div class="space-y-8">
            
            {{-- New Grid container for Start/End Time and Total Hours --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
                
                {{-- 1. Start Time --}}
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                    <input type="time" id="start_time" name="start_time" value="{{ old('start_time', '18:00') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                </div>

                {{-- 2. End Time --}}
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                    <input type="time" id="end_time" name="end_time" value="{{ old('end_time', '22:00') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                </div>
                
                {{-- 3. Total Hours (Calculated & Readonly) --}}
                <div>
                    <label for="total_hours" class="block text-sm font-medium text-gray-700">Total Hours</label>
                    {{-- READONLY: This input is calculated by JavaScript --}}
                    <input type="number" id="total_hours" name="total_hours" step="0.5" value="{{ old('total_hours', '4.0') }}" placeholder="4.0" class="mt-1 block w-full rounded-lg border-gray-400 bg-gray-100 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" readonly required>
                    <p class="mt-1 text-xs text-gray-500">Auto-calculated from start/end time.</p>
                </div>
            </div>

            {{-- Requirement Type & Job Code Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                
                {{-- Requirement Type --}}
                <div>
                    <label for="requirement_type" class="block text-sm font-medium text-gray-700">Requirement Type</label>
                    <select id="requirement_type" name="requirement_type" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                        <option value="" disabled selected>-- Select a type --</option>
                        <option value="Customer Requirement" {{ old('requirement_type') == 'Customer Requirement' ? 'selected' : '' }}>Customer Requirement</option>
                        <option value="RG Requirement" {{ old('requirement_type') == 'RG Requirement' ? 'selected' : '' }}>RG Requirement</option>
                    </select>
                </div>

                {{-- Job Code Input (Optional) --}}
                <div>
                    <label for="job_code" class="block text-sm font-medium text-gray-700">Job Code <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                    <input type="text" id="job_code" name="job_code" value="{{ old('job_code') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" placeholder="e.g. JC-2025-001">
                </div>
                
            </div>

            {{-- Reason for OT remains below --}}
            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700">Reason for OT</label>
                <textarea id="reason" name="reason" rows="4" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" placeholder="Describe the reason for this overtime work.">{{ old('reason') }}</textarea>
            </div>
        </div>

        {{-- Section 3: Team Members and Jobs Assignment --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
            <div>
                <label for="team_members" class="block text-sm font-medium text-gray-700">Assign Team Members</label>
                {{-- This select will be initialized with Tom Select --}}
                <select multiple id="team_members" name="team_members[]" placeholder="Select team members...">
                    {{-- employees list from controller --}}
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" data-name="{{ $employee->name }}" {{ (collect(old('team_members'))->contains($employee->id)) ? 'selected' : '' }}>
                            {{ $employee->name }} ({{$employee->employee_id}})
                        </option>
                    @endforeach
                </select>
                @error('team_members')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Assign Jobs</label>
                 <div id="jobs-container" class="mt-1 space-y-3 max-h-40 overflow-y-auto border border-gray-200 rounded-md p-3 bg-gray-50 min-h-[160px]">
                    <p id="placeholder-text" class="text-sm text-gray-500">Selected members' tasks will appear here.</p>
                 </div>
                 @error('tasks')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                 @enderror
            </div>
        </div>

        {{-- Submission Button --}}
        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-indigo-600 text-white py-2 px-6 rounded-md hover:bg-indigo-700">Submit Request</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{-- TomSelect CSS and JS files --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
/**
 * Function to calculate total hours between start and end time.
 * Handles overnight time differences (e.g., 22:00 to 02:00).
 */
function calculateHours() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const totalHoursInput = document.getElementById('total_hours');

    if (startTimeInput && endTimeInput && totalHoursInput && startTimeInput.value && endTimeInput.value) {
        // Use a fixed dummy date for Date object creation
        const dateString = '2000/01/01 '; 
        const startValue = startTimeInput.value;
        const endValue = endTimeInput.value;

        const startTime = new Date(dateString + startValue);
        let endTime = new Date(dateString + endValue);

        // Handle overnight OT
        if (endTime.getTime() <= startTime.getTime()) {
            endTime.setDate(endTime.getDate() + 1);
        }

        const diffMilliseconds = endTime - startTime;
        const diffHours = diffMilliseconds / (1000 * 60 * 60);

        // Round to nearest 0.5 hour
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

    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    if (startTimeInput) startTimeInput.addEventListener('input', calculateHours);
    if (endTimeInput) endTimeInput.addEventListener('input', calculateHours);
    
    // Existing TomSelect Logic
    const jobsContainer = document.getElementById('jobs-container');
    const placeholderText = document.getElementById('placeholder-text');

    // old() data handling for tasks
    const oldTasks = @json(old('tasks', []));

    const ts = new TomSelect('#team_members', {
        plugins: ['remove_button'],
        onItemAdd: function (value, item) {
            
            const rawText = item.textContent || item.innerText;
            const userName = rawText.split(' (')[0].trim();

            if (placeholderText) {
                placeholderText.style.display = 'none';
            }

            // Check if div already exists
            if (!document.getElementById('job-div-' + value)) {
                const jobInputDiv = document.createElement('div');
                jobInputDiv.id = 'job-div-' + value;
                
                // Check if there was an old value for this task
                const oldValue = oldTasks[value] || '';

                jobInputDiv.innerHTML = `
                    <div class="border-b border-gray-100 py-1">
                        <label for="job_${value}" class="block text-sm font-semibold text-gray-800">${userName}</label>
                        <input type="text" id="job_${value}" name="tasks[${value}]" 
                               value="${oldValue}"
                               class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-1.5 px-4 placeholder:text-gray-400" 
                               placeholder="Task for ${userName}" required>
                    </div>
                `;
                jobsContainer.appendChild(jobInputDiv);
            }
        },
        onItemRemove: function (value) {
            const jobInputDiv = document.getElementById('job-div-' + value);
            if (jobInputDiv) {
                jobInputDiv.remove();
            }

            if (this.items.length === 0 && placeholderText) {
                placeholderText.style.display = 'block';
            }
        }
    });

    // Trigger onItemAdd for pre-selected items (from old input) manually if needed, 
    // but TomSelect usually handles selection visual. We just need to ensure the Job Inputs are recreated.
    // The loop below ensures tasks inputs reappear after validation error.
    
    const selectedOptions = document.getElementById('team_members').options;
    for (let i = 0; i < selectedOptions.length; i++) {
        if (selectedOptions[i].selected) {
            // Manually trigger the logic to add the input field
            const value = selectedOptions[i].value;
            const text = selectedOptions[i].text;
            
            // Mocking the item object for onItemAdd logic
            const mockItem = { textContent: text };
            
            // Re-run logic to append input
            const rawText = mockItem.textContent;
            const userName = rawText.split(' (')[0].trim();

            if (placeholderText) placeholderText.style.display = 'none';

            if (!document.getElementById('job-div-' + value)) {
                const jobInputDiv = document.createElement('div');
                jobInputDiv.id = 'job-div-' + value;
                const oldValue = oldTasks[value] || '';

                jobInputDiv.innerHTML = `
                    <div class="border-b border-gray-100 py-1">
                        <label for="job_${value}" class="block text-sm font-semibold text-gray-800">${userName}</label>
                        <input type="text" id="job_${value}" name="tasks[${value}]" 
                               value="${oldValue}"
                               class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-1.5 px-4 placeholder:text-gray-400" 
                               placeholder="Task for ${userName}" required>
                    </div>
                `;
                jobsContainer.appendChild(jobInputDiv);
            }
        }
    }
});
</script>
@endpush