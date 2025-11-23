@extends('pages.layouts')

@section('content')
    <div class="page-card max-w-6xl mx-auto">
        
        {{-- Header and Filter Form --}}
        <div class="mb-6 pb-4 border-b">
            <h3 class="text-2xl font-bold">My OT Dashboard for: {{ auth()->user()->name }}</h3>
        </div>
        
        <form method="GET" action="{{ route('my-ot.view') }}" class="mb-8 p-4 bg-gray-50 rounded-lg shadow-inner flex space-x-4 items-end">
            {{-- Filter Inputs --}}
            <div class="flex-grow">
                <label for="month" class="block text-sm font-medium text-gray-700">Filter Month</label>
                <select id="month" name="month" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2 px-3">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @if((int)$currentMonth == $m) selected @endif>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="flex-grow">
                <label for="year" class="block text-sm font-medium text-gray-700">Filter Year</label>
                <select id="year" name="year" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2 px-3">
                    @for ($y = now()->year; $y >= now()->subYears(2)->year; $y--)
                        <option value="{{ $y }}" @if((int)$currentYear == $y) selected @endif>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-150">
                Filter
            </button>
        </form>
        
        {{-- Dashboard Content --}}
        <div>
            {{-- Total Hours Card --}}
            <div class="bg-indigo-500 text-white p-6 rounded-lg shadow-md mb-8">
                <h4 class="text-lg font-semibold">Total Actual Approved OT Hours ({{ \Carbon\Carbon::create()->month($currentMonth)->year($currentYear)->format('F Y') }})</h4>
                <p class="text-4xl font-bold mt-2">{{ $totalMonthlyHours }}</p>
                <p class="text-xs text-indigo-200 mt-1">* Calculated based on actual fingerprint data for approved requests.</p>
            </div>

            <h4 class="text-xl font-bold mb-4">My Assigned OT Jobs</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OT Job Date</th>
                            <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested Hrs</th>
                            
                            {{-- Actual Hours Column --}}
                            <th class="table-header px-6 py-3 text-left text-xs font-medium text-indigo-600 uppercase tracking-wider">Actual (Fingerprint)</th>
                            
                            <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                            <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assignedJobs as $job)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($job->otRequest->ot_date)->format('m/d/Y') }}
                            </td>
                            
                            {{-- Requested Hours --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                {{ $job->otRequest->total_hours }} hrs
                            </td>

                            {{-- [UPDATED] Actual Hours Logic: Only show if APPROVED --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($job->otRequest->status == 'approved')
                                    @php
                                        $dateKey = \Carbon\Carbon::parse($job->otRequest->ot_date)->format('Y-m-d');
                                        $fingerprint = $attendanceRecords[$dateKey] ?? null;
                                    @endphp

                                    @if($fingerprint)
                                        <span class="font-bold text-indigo-600">{{ $fingerprint->actual_ot_hours }} hrs</span>
                                        <div class="text-[10px] text-gray-400">
                                            In: {{ $fingerprint->check_in_time ? \Carbon\Carbon::parse($fingerprint->check_in_time)->format('H:i') : '-' }} | 
                                            Out: {{ $fingerprint->check_out_time ? \Carbon\Carbon::parse($fingerprint->check_out_time)->format('H:i') : '-' }}
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Not Imported</span>
                                    @endif
                                @else
                                    {{-- If not approved, show dash or empty --}}
                                    <span class="text-gray-300 text-xs">-</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ $job->task_description }}
                            </td>
                            
                            {{-- Status --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div>
                                    @if($job->otRequest->status == 'approved')
                                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                    @elseif($job->otRequest->status == 'pending')
                                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                    @else
                                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                    @endif
                                    
                                    <p class="text-xs text-gray-500 mt-1">
                                        Your status: 
                                        @if($job->employee_status == 'acknowledged')
                                            <span class="font-medium text-blue-600">Acknowledged</span>
                                        @else
                                            <span class="font-medium text-gray-700">OT Assigned</span>
                                        @endif
                                    </p>
                                </div>
                            </td>
                            
                            {{-- Action --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($job->employee_status == 'assigned')
                                     <form action="{{ route('my-ot.acknowledge', $job->id) }}" method="POST">
                                         @csrf
                                         <button type="submit" class="font-semibold text-indigo-600 hover:text-indigo-900">Acknowledge</button>
                                     </form>
                                @else
                                     <span class="text-gray-400">Done</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">No OT jobs assigned to you for this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection