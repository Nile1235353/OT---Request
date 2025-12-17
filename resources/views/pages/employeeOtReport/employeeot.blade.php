@extends('pages.layouts')

@section('content')
<div class="ot-report-container">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    {{-- 1. Page Title & Filters --}}
    <div class="page-card mb-6">
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-6">
            {{-- Title and Total Hours --}}
            <div class="flex-1 w-full xl:w-auto">
                <h1 class="text-2xl font-bold text-indigo-700">Employee OT Report</h1>
                <p class="text-sm text-gray-500 font-medium">(Approved Actual Data)</p>
                <div class="mt-2 text-lg font-bold text-gray-800 bg-indigo-50 inline-block px-3 py-1 rounded-lg border border-indigo-100">
                    Total: <span class="text-indigo-600">{{ number_format($totalHours, 2) }} hrs</span>
                </div>
            </div>
            
            {{-- Filter Form (Responsive Flex Layout) --}}
            <form method="GET" action="{{ url('/reports/employee-ot') }}" class="w-full xl:w-auto">
                <div class="flex flex-col sm:flex-row flex-wrap gap-4 items-end">
                    
                    {{-- Start Date --}}
                    <div class="w-full sm:w-auto flex-1 min-w-[140px]">
                        <label for="start_date" class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Start Date</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}" 
                                class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 w-full cursor-pointer h-[42px]">
                        </div>
                    </div>

                    {{-- End Date --}}
                    <div class="w-full sm:w-auto flex-1 min-w-[140px]">
                        <label for="end_date" class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">End Date</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}" 
                                class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 w-full cursor-pointer h-[42px]">
                        </div>
                    </div>

                    {{-- [NEW] Requirement Type Filter --}}
                    <div class="w-full sm:w-auto flex-1 min-w-[160px]">
                        <label for="requirement_type" class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Requirement</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            </div>
                            <select name="requirement_type" id="requirement_type" class="pl-10 pr-8 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 w-full appearance-none cursor-pointer h-[42px]">
                                <option value="">All Types</option>
                                {{-- Controller မှ လာသော Requirement Types များ --}}
                                @foreach($requirementTypes ?? [] as $type)
                                    <option value="{{ $type }}" {{ (request('requirement_type') == $type) ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Department Filter --}}
                    <div class="w-full sm:w-auto flex-1 min-w-[180px]">
                        <label for="department" class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Department</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <select name="department" id="department" class="pl-10 pr-8 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 w-full appearance-none cursor-pointer h-[42px]">
                                <option value="">All Departments</option>
                                @php
                                    $depts = ['Warehouse', 'ICD', 'Yard & Rail', 'Truck', 'IT', 'Process', 'Software', 'Data Center', 'Media', 'Secondary Transport', 'Business Development', 'Sales & CS', 'QEHS', 'Admin & HR', 'Finance & Account', 'M&E', 'Management', 'M&R', 'Customs & Formalities','Corportate'];
                                @endphp
                                @foreach($depts as $dept)
                                    <option value="{{ $dept }}" {{ (request('department') == $dept) ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="w-full sm:w-auto flex gap-2">
                        <button type="submit" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors h-[42px]">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Filter
                        </button>
                        <a href="{{ url('/reports/employee-ot') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors h-[42px]">
                            Clear
                        </a>
                        <a href="#" id="export-btn" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors h-[42px]">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Export
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. Report Table --}}
    <div class="page-card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OT Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Code</th>
                    {{-- [NEW] Requirement Type Column --}}
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requirement</th>
                    
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Actual In/Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Actual Hours</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($assignedOts as $item) 
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ Carbon\Carbon::parse($item->otRequest->ot_date)->format('M d, Y') }}
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">
                            {{ $item->otRequest->job_code ?? '-' }}
                        </td>

                        {{-- [NEW] Requirement Type Data --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $item->otRequest->requirement_type ?? '-' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                            {{ $item->user->department ?? '-' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="font-medium text-gray-900">{{ $item->user->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $item->user->employee_id ?? '-' }}</div>
                            <div class="text-xs text-gray-400">FP: {{ $item->user->finger_print_id ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $item->otRequest->supervisor->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700" style="min-width: 150px; max-width: 200px; white-space: normal;">
                            {{ $item->otRequest->reason }}
                        </td>
                        
                        <td class="px-6 py-4 text-sm text-gray-700" style="min-width: 150px; max-width: 200px; white-space: normal;">
                            {{ $item->task_description }}
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if($item->actual_in && $item->actual_out)
                                <span class="text-green-600 font-medium">{{ Carbon\Carbon::parse($item->actual_in)->format('H:i') }}</span> - 
                                <span class="text-red-600 font-medium">{{ Carbon\Carbon::parse($item->actual_out)->format('H:i') }}</span>
                            @else
                                <span class="text-gray-400 italic">No Data</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                            {{ number_format($item->actual_hours, 2) }} hrs
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                {{ $item->otRequest->status }}
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button type="button" 
                                    class="text-indigo-600 hover:text-indigo-900 font-semibold edit-task-btn bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded transition-colors"
                                    data-id="{{ $item->id }}"
                                    data-task="{{ $item->task_description }}"
                                    data-user="{{ $item->user->name }}">
                                Edit Task
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-6 py-12 text-center text-sm text-gray-500">
                            No approved actual OT records found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($assignedOts->count() > 0)
            <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                <tr>
                    {{-- Colspan increased to 9 to align --}}
                    <td colspan="9" class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-700 uppercase">
                        Total Actual Hours:
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-700 text-base">
                        {{ number_format($totalHours, 2) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Edit Task Modal (Same as before) --}}
<div id="editTaskModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- ... Modal content remains unchanged ... --}}
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div id="editTaskBackdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="editTaskForm" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Edit Task for <span id="modalEmployeeName" class="font-bold text-indigo-600"></span>
                            </h3>
                            <div class="mt-4">
                                <label for="task_description" class="block text-sm font-medium text-gray-700">Task Description</label>
                                <div class="mt-1">
                                    <textarea id="task_description" name="task_description" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Save Changes</button>
                    <button type="button" id="closeEditModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Export Logic
        const exportBtn = document.getElementById('export-btn');
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        const deptInput = document.querySelector('select[name="department"]');
        const reqTypeInput = document.querySelector('select[name="requirement_type"]'); // [NEW] Req Type Input
        const baseUrl = "{{ route('reports.employee_ot.export') }}";

        function updateExportLink() {
            const params = new URLSearchParams();
            if (startDateInput.value) params.append('start_date', startDateInput.value);
            if (endDateInput.value) params.append('end_date', endDateInput.value);
            if (deptInput.value) params.append('department', deptInput.value);
            if (reqTypeInput.value) params.append('requirement_type', reqTypeInput.value); // [NEW] Add to Export
            exportBtn.href = baseUrl + '?' + params.toString();
        }

        updateExportLink();
        startDateInput.addEventListener('change', updateExportLink);
        endDateInput.addEventListener('change', updateExportLink);
        deptInput.addEventListener('change', updateExportLink);
        reqTypeInput.addEventListener('change', updateExportLink); // [NEW] Listener

        // Edit Modal Logic (Same as before)
        const modal = document.getElementById('editTaskModal');
        const closeBtn = document.getElementById('closeEditModal');
        const backdrop = document.getElementById('editTaskBackdrop');
        const modalEmployeeName = document.getElementById('modalEmployeeName');
        const taskInput = document.getElementById('task_description');
        const form = document.getElementById('editTaskForm');
        const editButtons = document.querySelectorAll('.edit-task-btn');

        function openModal() { modal.classList.remove('hidden'); }
        function closeModal() { modal.classList.add('hidden'); }

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const task = this.dataset.task;
                const userName = this.dataset.user;
                modalEmployeeName.textContent = userName;
                taskInput.value = task;
                form.action = `/assign-team/${id}/update-task`;
                openModal();
            });
        });

        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);
    });
</script>
@endpush