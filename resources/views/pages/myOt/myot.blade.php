@extends('pages.layouts')

@section('content')
    <div class="page-card max-w-6xl mx-auto">
        <div class="mb-6 pb-4 border-b">
            <h3 class="text-2xl font-bold">My OT Dashboard for: {{ auth()->user()->name }}</h3>
        </div>
        <div>
            <div class="bg-indigo-500 text-white p-6 rounded-lg shadow-md mb-8">
                <h4 class="text-lg font-semibold">Total Approved OT Hours (This Month)</h4>
                <p class="text-4xl font-bold mt-2">{{ $totalMonthlyHours }}</p>
            </div>
            <h4 class="text-xl font-bold mb-4">My Assigned OT Jobs</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="table-header">OT Job Date</th>
                            <th class="table-header">Hours</th>
                            <th class="table-header">Task</th>
                            <th class="table-header">Status</th>
                            <th class="table-header">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assignedJobs as $job)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($job->otRequest->ot_date)->format('m/d/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $job->otRequest->total_hours }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ $job->task_description }}
                            </td>
                            
                            {{-- Change 1: Updated Status Display --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div>
                                    @if($job->otRequest->status == 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @elseif($job->otRequest->status == 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Rejected
                                        </span>
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
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{-- Change 2: Simplified Acknowledge Condition --}}
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
                            <td colspan="5" class="text-center py-8 text-gray-500">No OT jobs assigned to you yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection