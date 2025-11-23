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
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            {{-- Title and Total Hours --}}
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-indigo-700">Employee OT Report (Approved Actual)</h1>
                <p class="mt-1 text-lg font-semibold text-gray-800">
                    Total Actual Hours: 
                    <span class="text-indigo-600">{{ number_format($totalHours, 2) }} hrs</span>
                </p>
            </div>
            
            {{-- Filter Form --}}
            <form method="GET" action="{{ url('/reports/employee-ot') }}" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <div class="flex-1">
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex-1">
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Filter
                    </button>
                    <a href="{{ url('/reports/employee-ot') }}" class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Clear
                    </a>
                    <a href="#" id="export-btn" class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        Export
                    </a>
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
                    {{-- [NEW] Job Code Column --}}
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Code</th>
                    
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
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ Carbon\Carbon::parse($item->otRequest->ot_date)->format('M d, Y') }}
                        </td>
                        
                        {{-- [NEW] Job Code Data --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">
                            {{ $item->otRequest->job_code ?? '-' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $item->user->name ?? 'N/A' }}
                            <span class="block text-xs text-gray-400">FP: {{ $item->user->finger_print_id ?? '-' }}</span>
                            <span class="block text-xs text-gray-400">{{ $item->user->department ?? '' }}</span>
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
                                <span class="text-green-600">{{ Carbon\Carbon::parse($item->actual_in)->format('H:i') }}</span> - 
                                <span class="text-red-600">{{ Carbon\Carbon::parse($item->actual_out)->format('H:i') }}</span>
                            @else
                                <span class="text-gray-400 italic">No Data</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                            {{ number_format($item->actual_hours, 2) }} hrs
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $item->otRequest->status }}
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button type="button" 
                                    class="text-indigo-600 hover:text-indigo-900 font-semibold edit-task-btn"
                                    data-id="{{ $item->id }}"
                                    data-task="{{ $item->task_description }}"
                                    data-user="{{ $item->user->name }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        {{-- Colspan increased to 10 --}}
                        <td colspan="10" class="px-6 py-12 text-center text-sm text-gray-500">
                            No approved actual OT records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($assignedOts->count() > 0)
            <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                <tr>
                    {{-- Colspan adjusted for footer alignment (7 columns before 'Actual Hours') --}}
                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-700 uppercase">
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

{{-- ... Edit Modal Code Remains Same ... --}}
{{-- INCLUDE YOUR EXISTING MODAL & SCRIPT HERE --}}
<div id="editTaskModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div id="editTaskBackdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="editTaskForm" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
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
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">Save Changes</button>
                    <button type="button" id="closeEditModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
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
        const baseUrl = "{{ route('reports.employee_ot.export') }}";

        function updateExportLink() {
            const params = new URLSearchParams();
            if (startDateInput.value) params.append('start_date', startDateInput.value);
            if (endDateInput.value) params.append('end_date', endDateInput.value);
            exportBtn.href = baseUrl + '?' + params.toString();
        }

        updateExportLink();
        startDateInput.addEventListener('change', updateExportLink);
        endDateInput.addEventListener('change', updateExportLink);

        // Edit Modal Logic
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