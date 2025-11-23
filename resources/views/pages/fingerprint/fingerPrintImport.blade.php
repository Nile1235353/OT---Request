@extends('pages.layouts')

@section('content')
<div class="ot-report-container">

    {{-- Display Success/Error Messages --}}
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
            <div class="flex gap-2">
                <form method="GET" action="{{ url('/ot-attendance') }}" class="flex gap-2">
                    <input type="date" name="filter_date" value="{{ request('filter_date') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <button type="submit" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded text-gray-600 hover:bg-gray-200 text-sm">Search</button>
                </form>

                {{-- IMPORT BUTTON --}}
                <button id="open-import-modal" class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imported At</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($attendanceData as $record) 
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Carbon\Carbon::parse($record->date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                            {{-- Relationship နဲ့ ခေါ်ပြထားခြင်း --}}
                            {{ $record->user->name ?? '-' }} 
                            <span class="text-xs text-gray-400 block">ID: {{ $record->employee_id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                            {{ $record->check_in_time ? Carbon\Carbon::parse($record->check_in_time)->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                            {{ $record->check_out_time ? Carbon\Carbon::parse($record->check_out_time)->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                            {{-- UPDATED: Negative တန်ဖိုးဖြစ်နေရင် 0 ပြမယ် --}}
                            {{ $record->actual_ot_hours < 0 ? 0 : $record->actual_ot_hours }} hrs
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                            {{ $record->created_at->diffForHumans() }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                            No data found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $attendanceData->links() }}
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL BOX --}}
{{-- ========================================== --}}
<div id="import-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-backdrop" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            
            <form action="{{ route('ot.attendance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                Import & Calculate OT
                            </h3>
                            
                            {{-- Office Hours Inputs --}}
                            <div class="mt-4 grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-md border border-gray-200">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">ရုံးတက်ချိန် (Office Start)</label>
                                    <input type="time" name="office_start_time" value="09:00" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">ရုံးဆင်းချိန် (Office End)</label>
                                    <input type="time" name="office_end_time" value="17:00" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <p class="col-span-2 text-xs text-gray-500 italic">
                                    * သတ်မှတ်ထားသော ရုံးချိန်ပြင်ပ နာရီများကို OT အဖြစ် တွက်ချက်ပါမည်။
                                </p>
                            </div>

                            {{-- File Upload --}}
                            <div class="mt-4">
                                <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-500 transition-colors">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                                <span>Select Excel File</span>
                                                <input id="file-upload" name="file" type="file" class="sr-only" required accept=".xlsx, .xls, .csv">
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500" id="file-name-display">
                                            .xlsx, .csv only
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Start Import
                    </button>
                    <button type="button" id="close-import-modal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('import-modal');
        const openBtn = document.getElementById('open-import-modal');
        const closeBtn = document.getElementById('close-import-modal');
        const fileInput = document.getElementById('file-upload');
        const fileNameDisplay = document.getElementById('file-name-display');

        openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
        
        modal.addEventListener('click', (e) => {
            if (e.target.id === 'modal-backdrop') modal.classList.add('hidden');
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileNameDisplay.textContent = e.target.files[0].name;
                fileNameDisplay.className = "text-xs text-green-600 font-bold mt-1";
            }
        });
    });
</script>
@endpush