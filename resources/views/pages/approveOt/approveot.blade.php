@extends('pages.layouts')

@section('content')

    <div class="space-y-8">
        {{-- PENDING APPROVALS SECTION --}}
        <div class="page-card">
            <h3 class="text-2xl font-bold mb-6 border-b pb-3">Pending OT Approvals</h3>
            <div class="space-y-4">
                @forelse($pendingRequests as $request)
                    <div class="border border-gray-200 p-4 rounded-lg bg-white grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <div class="md:col-span-3 space-y-3">
                            <p><span class="font-bold">Date:</span> {{ \Carbon\Carbon::parse($request->ot_date)->format('m/d/Y') }}</p>
                            <p><span class="font-bold">Title:</span> {{ $request->title }}</p>
                            <p><span class="font-bold">Requester:</span> {{ $request->supervisor->name ?? 'N/A' }}</p>
                            <p><span class="font-bold">Reason:</span> {{ $request->reason }}</p>
                            <div>
                                <h4 class="font-semibold text-sm">Assigned Team:</h4>
                                <ul class="list-disc list-inside text-sm text-gray-600 mt-1">
                                    @foreach($request->assignedUsers as $assignedUser)
                                        <li>{{ $assignedUser->user->name ?? 'N/A' }}: {{ $assignedUser->task_description }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="flex flex-col space-y-2 justify-center">
                            <form action="{{ route('approvals.approve', $request->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Approve</button>
                            </form>
                            <form action="{{ route('approvals.reject', $request->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Reject</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">There are no pending approvals at this time.</p>
                @endforelse
            </div>
        </div>

        {{-- REQUEST HISTORY SECTION --}}
        <div class="page-card">
             <div class="flex justify-between items-center mb-6 border-b pb-3">
                <h3 class="text-2xl font-bold">Request History</h3>
                {{-- <button class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Export to Excel</button> --}}
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="table-header">Date</th>
                            <th class="table-header">Requested By</th>
                            <th class="table-header">Title</th>
                            <th class="table-header">Hours</th>
                            <th class="table-header">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($historyRequests as $history)
                        <tr>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($history->ot_date)->format('m/d/Y') }}</td>
                            <td class="px-6 py-4">{{ $history->supervisor->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $history->title }}</td>
                            <td class="px-6 py-4">{{ $history->total_hours }}</td>
                            <td class="px-6 py-4">
                                @if($history->status == 'approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                @elseif($history->status == 'rejected')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-500">No history found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection