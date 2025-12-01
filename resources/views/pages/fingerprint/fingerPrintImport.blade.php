@extends('pages.layouts')

@section('content')
<div class="ot-report-container">

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    {{-- 1. Page Title & Actions --}}
    <div class="page-card mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            
            {{-- Title --}}
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-indigo-700">OT Actual Detail</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Fingerprint Data များကို Excel ဖြင့် Import ပြုလုပ်ပြီး OT တွက်ချက်ရန်။
                </p>
            </div>
            
            {{-- Actions Button Group --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <form method="GET" action="{{ url('/ot-attendance') }}" class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input type="date" name="filter_date" value="{{ request('filter_date') }}" class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-l-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 h-10 w-full sm:w-auto cursor-pointer shadow-sm">
                    <button type="submit" class="-ml-px px-4 py-2 bg-white border border-gray-200 rounded-r-lg text-sm font-medium text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all duration-200 h-10 shadow-sm flex items-center group">
                        <svg class="w-4 h-4 mr-1.5 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Search
                    </button>
                </form>

                <button id="open-import-modal" class="inline-flex justify-center items-center px-5 py-2 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 h-10">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import Excel
                </button>
            </div>
        </div>
    </div>

    {{-- 2. Data Table --}}
    <div class="page-card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OT Hours</th>
                    {{-- Action Column --}}
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($attendanceData as $record) 
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Carbon\Carbon::parse($record->date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                            {{ $record->user->name ?? '-' }} 
                            <span class="text-xs text-gray-400 block">ID: {{ $record->employee_id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                            {{ $record->check_in_time ? Carbon\Carbon::parse($record->check_in_time)->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                            {{ $record->check_out_time ? Carbon\Carbon::parse($record->check_out_time)->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                            {{ $record->actual_ot_hours < 0 ? 0 : $record->actual_ot_hours }} hrs
                        </td>
                        
                        {{-- Edit Button --}}
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <button type="button" 
                                class="edit-attendance-btn text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-full transition-colors"
                                data-id="{{ $record->id }}"
                                data-date="{{ $record->date }}"
                                data-name="{{ $record->user->name ?? 'Unknown' }}"
                                data-checkin="{{ $record->check_in_time }}" 
                                data-checkout="{{ $record->check_out_time }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                            No attendance data found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4 px-4 pb-4">
            {{ $attendanceData->links() }}
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL 1: IMPORT EXCEL --}}
{{-- ========================================== --}}
<div id="import-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-backdrop"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('ot.attendance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900">Import & Calculate OT</h3>
                            <div class="mt-4 grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-md border border-gray-200">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Office Start</label>
                                    <input type="time" name="office_start_time" value="09:00" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Office End</label>
                                    <input type="time" name="office_end_time" value="17:00" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Excel File</label>
                                <input type="file" name="file" required accept=".xlsx, .xls, .csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">Import</button>
                    <button type="button" id="close-import-modal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL 2: EDIT ATTENDANCE --}}
{{-- ========================================== --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div id="edit-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all scale-100">
            
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800">Edit Attendance Record</h3>
                <button id="close-edit-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                
                {{-- Employee Name (Read Only) --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Employee</label>
                    <input type="text" id="edit_name" class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded text-gray-600 text-sm font-medium" disabled>
                </div>

                {{-- Date (Editable) --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                    <input type="date" name="date" id="edit_date" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                </div>

                {{-- [NEW] Office Hours for Recalculation --}}
                <div class="grid grid-cols-2 gap-4 mb-4 bg-yellow-50 p-3 rounded border border-yellow-200">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Office Start</label>
                        <input type="time" name="office_start_time" id="edit_office_start" value="09:00" class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-yellow-500 focus:border-yellow-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Office End</label>
                        <input type="time" name="office_end_time" id="edit_office_end" value="17:00" class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-yellow-500 focus:border-yellow-500 text-sm" required>
                    </div>
                    <p class="col-span-2 text-xs text-yellow-600 italic text-center">OT will be recalculated based on these hours.</p>
                </div>

                {{-- Time Inputs --}}
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Check In</label>
                        <input type="time" name="check_in_time" id="edit_check_in" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-green-500 focus:border-green-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Check Out</label>
                        <input type="time" name="check_out_time" id="edit_check_out" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-red-500 focus:border-red-500 text-sm">
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" id="cancel-edit" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm">Update & Recalculate</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Import Modal Logic ---
        const importModal = document.getElementById('import-modal');
        const openImportBtn = document.getElementById('open-import-modal');
        const closeImportBtn = document.getElementById('close-import-modal');
        
        openImportBtn.addEventListener('click', () => importModal.classList.remove('hidden'));
        closeImportBtn.addEventListener('click', () => importModal.classList.add('hidden'));
        importModal.addEventListener('click', (e) => {
            if (e.target.id === 'modal-backdrop') importModal.classList.add('hidden');
        });

        // --- 2. Edit Modal Logic ---
        const editModal = document.getElementById('edit-modal');
        const editForm = document.getElementById('edit-form');
        const closeEditBtn = document.getElementById('close-edit-modal');
        const cancelEditBtn = document.getElementById('cancel-edit');
        const editBackdrop = document.getElementById('edit-backdrop');
        const editButtons = document.querySelectorAll('.edit-attendance-btn');

        // Function to Open Edit Modal & Populate Data
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const date = this.dataset.date;
                const checkin = this.dataset.checkin ? this.dataset.checkin.substring(0, 5) : ''; 
                const checkout = this.dataset.checkout ? this.dataset.checkout.substring(0, 5) : '';

                // Set Form Action
                editForm.action = `/ot-attendance/${id}`; 

                // Fill Inputs
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_date').value = date;
                document.getElementById('edit_check_in').value = checkin;
                document.getElementById('edit_check_out').value = checkout;
                
                // Reset Office Times to Default (You can change defaults here if needed)
                document.getElementById('edit_office_start').value = '09:00';
                document.getElementById('edit_office_end').value = '17:00';

                editModal.classList.remove('hidden');
            });
        });

        // Close Functions
        function closeEdit() { editModal.classList.add('hidden'); }
        closeEditBtn.addEventListener('click', closeEdit);
        cancelEditBtn.addEventListener('click', closeEdit);
        editBackdrop.addEventListener('click', closeEdit);
    });
</script>
@endpush