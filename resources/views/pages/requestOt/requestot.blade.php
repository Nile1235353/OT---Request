@extends('pages.layouts')

@section('content')

<div class="page-card max-w-4xl mx-auto">
    <h3 class="text-3xl font-bold mb-8 text-gray-800">New Overtime Request</h3>

    <form action="{{ route('overtime.store') }}" method="POST" class="space-y-8">
        @csrf
        
        {{-- Section 1: Supervisor and Date --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
            <div>
                <label class="block text-sm font-medium text-gray-700">Requesting Supervisor</label>
                <div class="mt-1 p-2 border border-gray-200 bg-gray-50 rounded-md">
                    {{-- Login ဝင်ထားတဲ့ user ရဲ့ နာမည်ကို တိုက်ရိုက်ပြသပေးမှာဖြစ်ပါတယ် --}}
                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                </div>
            </div>
            <div>
                <label for="ot_date" class="block text-sm font-medium text-gray-700">OT Date</label>
                <input type="date" id="ot_date" name="ot_date" value="{{ now()->format('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400">
            </div>
        </div>

        {{-- Section 2: Hours , Requirement Type and Reason --}}
        <div class="space-y-8">
    
            {{-- New Grid container for side-by-side fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                
                {{-- First Column: Total Hours --}}
                <div>
                    <label for="total_hours" class="block text-sm font-medium text-gray-700">Total Hours</label>
                    <input type="number" id="total_hours" name="total_hours" step="0.5" placeholder="e.g., 4" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400">
                </div>

                {{-- Second Column: Requirement Type --}}
                <div>
                    <label for="requirement_type" class="block text-sm font-medium text-gray-700">Requirement Type</label>
                    <select id="requirement_type" name="requirement_type" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" required>
                        <option value="" disabled selected>-- Select a type --</option>
                        <option value="Customer Requirement">Customer Requirement</option>
                        <option value="RG Requirement">RG Requirement</option>
                    </select>
                </div>
            </div>

            {{-- Reason for OT remains below, full width --}}
            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700">Reason for OT</label>
                <textarea id="reason" name="reason" rows="4" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400" placeholder="Describe the reason for this overtime work."></textarea>
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
                        <option value="{{ $employee->id }}" data-name="{{ $employee->name }}">{{ $employee->name }} ({{$employee->employee_id}})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Assign Jobs</label>
                 <div id="jobs-container" class="mt-1 space-y-3 max-h-40 overflow-y-auto border border-gray-200 rounded-md p-3 bg-gray-50 min-h-[160px]">
                    <p id="placeholder-text" class="text-sm text-gray-500">Selected members' tasks will appear here.</p>
                 </div>
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
<script>
document.addEventListener("DOMContentLoaded", function () {
    const jobsContainer = document.getElementById('jobs-container');
    const placeholderText = document.getElementById('placeholder-text');

    new TomSelect('#team_members', {
        plugins: ['remove_button'],
        // Function to run when a user is selected
        onItemAdd: function (value, item) {
            
            // FIX for 'undefined' error:
            // Get name from the option's text content and remove the employee ID part.
            // Example: "Poe Pyae Pyae Chaw (RG-001)" becomes "Poe Pyae Pyae Chaw"
            const rawText = item.textContent || item.innerText;
            const userName = rawText.split(' (')[0];

            // Hide placeholder if it exists
            if (placeholderText) {
                placeholderText.style.display = 'none';
            }

            // Create a new div for the job input
            const jobInputDiv = document.createElement('div');
            jobInputDiv.id = 'job-div-' + value;
            
            // STYLE UPDATE to match the image and the rest of your form
            jobInputDiv.innerHTML = `
                <div class="border-b border-gray-100">
                    <label for="job_${value}" class="block text-sm font-semibold text-gray-800">${userName}</label>
                    <input type="text" id="job_${value}" name="tasks[${value}]" 
                           class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 px-4 placeholder:text-gray-400" 
                           placeholder="Task for ${userName}" required>
                </div>
            `;
            jobsContainer.appendChild(jobInputDiv);
        },
        // Function to run when a user is deselected
        onItemRemove: function (value) {
            const jobInputDiv = document.getElementById('job-div-' + value);
            if (jobInputDiv) {
                jobInputDiv.remove();
            }

            // Show placeholder if no members are selected
            // 'this' refers to the TomSelect instance here
            if (this.items.length === 0 && placeholderText) {
                placeholderText.style.display = 'block';
            }
        }
    });
});
</script>
@endpush