@extends('pages.layouts')

@section('content')

{{-- MODAL STYLES --}}
<style>
    /* General Modal Overlay */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.6);
        display: none; /* Hidden by default */
        z-index: 999;
        overflow-y: auto; 
    }

    /* 1. Edit Modal Specifics */
    .edit-modal-overlay {
        align-items: flex-start; 
        padding-top: 5vh;
        justify-content: center;
    }

    /* 2. Reject Modal Specifics */
    .reject-modal-overlay {
        align-items: center;
        justify-content: center;
    }

    /* Modal Content Box */
    .modal-content {
        background-color: white;
        padding: 1.5rem 2rem;
        border-radius: 0.5rem;
        z-index: 1000;
        width: 90%;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        margin-bottom: 5vh;
        animation: fadeIn 0.3s;
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .edit-modal-content {
        max-width: 700px; /* Wider for Edit */
    }

    .reject-modal-content {
        max-width: 500px; /* Narrower for Reject */
    }

    /* New User Entry Style */
    .new-user-entry {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        border: 1px solid #e0e0e0;
        padding: 0.75rem;
        border-radius: 0.375rem;
        background-color: #f9fafb;
    }
</style>

<div class="space-y-8">
    {{-- PENDING APPROVALS SECTION --}}
    <div class="page-card">
        <h3 class="text-2xl font-bold mb-6 border-b pb-3 text-indigo-700">Pending OT Approvals</h3>
        <div class="space-y-4">
            @forelse($pendingRequests as $request)
                <div class="border border-gray-200 p-5 rounded-lg bg-white grid grid-cols-1 md:grid-cols-4 gap-6 items-start shadow-sm hover:shadow-md transition-shadow">
                    <div class="md:col-span-3 space-y-2">
                        <div class="flex items-center gap-4 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $request->request_id }}
                            </span>
                            <span class="text-sm text-gray-500">
                                <span class="font-semibold text-gray-700">Date:</span> 
                                {{ \Carbon\Carbon::parse($request->ot_date)->format('M d, Y') }}
                            </span>
                        </div>

                        {{-- [NEW] Time & Hours Display --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm bg-gray-50 p-3 rounded-md border border-gray-100">
                            <div>
                                <span class="block text-xs font-bold text-gray-500 uppercase">Est. Time</span>
                                <span class="font-semibold text-gray-800">
                                    {{ \Carbon\Carbon::parse($request->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($request->end_time)->format('h:i A') }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-500 uppercase">Total Hours</span>
                                <span class="font-bold text-indigo-600">{{ $request->total_hours }} Hrs</span>
                            </div>
                        </div>

                        <p class="text-sm"><span class="font-bold text-gray-700">Requester:</span> {{ $request->supervisor->name ?? 'N/A' }}</p>
                        <p class="text-sm"><span class="font-bold text-gray-700">Reason:</span> <span class="italic text-gray-600">{{ $request->reason }}</span></p>
                        
                        <div class="mt-3">
                            <h4 class="font-bold text-sm text-gray-800 mb-1">Assigned Team:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1 ml-1">
                                @foreach($request->assignedUsers as $assignedUser)
                                    <li>
                                        <span class="font-medium text-gray-900">{{ $assignedUser->user->name ?? 'N/A' }}</span>
                                        {{-- [NEW] Department Display --}}
                                        <span class="text-xs text-gray-500">({{ $assignedUser->user->department ?? '-' }})</span>
                                        : <span class="text-gray-700">{{ $assignedUser->task_description }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-col space-y-3 justify-center h-full pt-2">
                        {{-- Edit & Approve Button --}}
                        <button type="button" 
                                onclick="openEditModal('editModal-{{ $request->id }}')" 
                                class="w-full px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-md hover:bg-indigo-700 shadow-sm transition-colors">
                            Edit & Approve
                        </button>
                        
                        {{-- Reject Button --}}
                        <button type="button" 
                                onclick="openRejectModal('{{ $request->id }}')" 
                                class="w-full px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-md hover:bg-red-700 shadow-sm transition-colors">
                            Reject
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-gray-500">There are no pending approvals at this time.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- REQUEST HISTORY SECTION --}}
    <div class="page-card">
         <div class="flex justify-between items-center mb-6 border-b pb-3">
            <h3 class="text-2xl font-bold text-gray-800">Request History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reject Remark</th>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($historyRequests as $history)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ \Carbon\Carbon::parse($history->ot_date)->format('m/d/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $history->supervisor->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                            {{ $history->total_hours }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($history->status == 'approved')
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                            @elseif($history->status == 'rejected')
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 text-sm text-gray-500 italic max-w-xs truncate">
                            {{ $history->reject_remark ?? '-' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($history->status == 'approved')
                                <button type="button" 
                                        onclick="openRejectModal('{{ $history->id }}')" 
                                        class="px-3 py-1 text-xs font-bold text-white bg-red-600 rounded hover:bg-red-700 transition-colors">
                                    Reject
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-500">No history found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================================================================= --}}
{{-- 1. EDIT MODALS (Generated Loop) --}}
{{-- ================================================================= --}}
@foreach($pendingRequests as $request)
    <div id="editModal-{{ $request->id }}" class="modal-overlay edit-modal-overlay" onclick="closeModal('editModal-{{ $request->id }}')">
        <div class="modal-content edit-modal-content" onclick="event.stopPropagation();">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h4 class="text-xl font-bold text-indigo-700">Edit & Approve OT Request</h4>
                <button onclick="closeModal('editModal-{{ $request->id }}')" class="text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4 text-sm bg-gray-50 p-3 rounded">
                <p><span class="font-bold text-gray-700">ID:</span> {{ $request->request_id }}</p>
                <p><span class="font-bold text-gray-700">Date:</span> {{ \Carbon\Carbon::parse($request->ot_date)->format('m/d/Y') }}</p>
                <p><span class="font-bold text-gray-700">Time:</span> {{ \Carbon\Carbon::parse($request->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('h:i A') }}</p>
                <p><span class="font-bold text-gray-700">Total:</span> {{ $request->total_hours }} Hrs</p>
            </div>

            <form action="{{ route('approvals.approve', $request->id) }}" method="POST">
                @csrf
                
                <h5 class="font-bold mb-2 text-gray-700 text-sm uppercase tracking-wide">Current Team</h5>
                <div class="space-y-3 mb-6 max-h-[40vh] overflow-y-auto pr-2 border border-gray-200 rounded p-3 bg-white">
                    @foreach($request->assignedUsers as $assignedUser)
                        <div class="border border-gray-100 p-3 rounded-md bg-gray-50 hover:bg-gray-100 transition-colors">
                            <label class="font-semibold text-gray-800 block">
                                {{ $assignedUser->user->name ?? 'N/A' }}
                                {{-- [NEW] Department in Modal --}}
                                <span class="text-xs font-normal text-gray-500">({{ $assignedUser->user->department ?? '-' }})</span>
                            </label>
                            <input type="hidden" name="users[{{ $assignedUser->user->id }}][id]" value="{{ $assignedUser->user->id }}">
                            
                            <label class="block text-xs font-medium text-gray-500 mt-2 mb-1">Task Description:</label>
                            <input type="text" name="users[{{ $assignedUser->user->id }}][task_description]" 
                                   value="{{ $assignedUser->task_description }}" 
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            
                            <div class="mt-2 text-right">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="users[{{ $assignedUser->user->id }}][remove]" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-xs text-red-600 hover:text-red-800 font-bold uppercase">Remove User</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <h5 class="font-bold mb-2 text-gray-700 text-sm uppercase tracking-wide">Add New User</h5>
                <div class="border border-indigo-100 p-4 rounded-md bg-indigo-50 mb-6 space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Select User to Add:</label>
                    <select id="newUserSelect-{{ $request->id }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" 
                            onchange="addNewUserField(this, 'new-users-container-{{ $request->id }}')">
                        <option value="">-- Select --</option>
                        @if(isset($allUsers))
                            @foreach($allUsers as $user)
                                @if(!$request->assignedUsers->pluck('user_id')->contains($user->id))
                                    <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }} ({{ $user->department }})</option>
                                @endif
                            @endforeach
                        @else
                            <option value="" disabled>User list not available</option>
                        @endif
                    </select>
                    <div id="new-users-container-{{ $request->id }}" class="space-y-3 mt-3"></div>
                </div>
                
                <div class="flex justify-end space-x-3 border-t pt-4">
                    <button type="button" onclick="closeModal('editModal-{{ $request->id }}')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-indigo-600 rounded-md hover:bg-indigo-700 shadow-md transition-colors">
                        Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- ================================================================= --}}
{{-- 2. SHARED REJECT MODAL --}}
{{-- ================================================================= --}}
<div id="sharedRejectModal" class="modal-overlay reject-modal-overlay" onclick="closeRejectModal()">
    <div class="modal-content reject-modal-content" onclick="event.stopPropagation();">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h4 class="text-xl font-bold text-red-600">Reject OT Request</h4>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
        </div>

        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label for="reject_remark_input" class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection <span class="text-red-500">*</span></label>
                <textarea id="reject_remark_input" name="reject_remark" rows="4" 
                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 p-3" 
                          placeholder="Please explain why this request is being rejected..." required></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-md hover:bg-red-700 shadow-sm transition-colors">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // --- Modal Control Functions ---

    // General Close Function
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // 1. Open Edit Modal
    function openEditModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) {
            modal.style.display = 'flex'; // Flex allows CSS centering properties to work
            document.body.style.overflow = 'hidden';
        }
    }

    // 2. Open Reject Modal
    const rejectModal = document.getElementById('sharedRejectModal');
    const rejectForm = document.getElementById('rejectForm');
    const rejectRemarkInput = document.getElementById('reject_remark_input');

    function openRejectModal(requestId) {
        // Reset Input
        if(rejectRemarkInput) rejectRemarkInput.value = '';
        
        // Set Dynamic Action
        if(rejectForm) rejectForm.action = `/approve-ot/${requestId}/reject`;
        
        // Show Modal
        if(rejectModal) {
            rejectModal.style.display = 'flex'; // CSS .reject-modal-overlay handles centering
            document.body.style.overflow = 'hidden';
        }
    }

    function closeRejectModal() {
        if(rejectModal) {
            rejectModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // --- Dynamic User Field Functions ---

    function addNewUserField(selectElement, containerId) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const userId = selectedOption.value;
        const userName = selectedOption.dataset.name;

        if (!userId) return;

        const container = document.getElementById(containerId);
        if (document.getElementById('new-user-entry-' + userId)) return;

        const newFieldHTML = `
            <div id="new-user-entry-${userId}" class="new-user-entry bg-white border border-indigo-200" data-user-id="${userId}">
                <div class="flex justify-between items-center border-b border-gray-100 pb-1 mb-1">
                    <label class="font-semibold text-indigo-700 text-sm">${userName}</label>
                    <button type="button" onclick="removeNewUserField('${userId}', '${selectElement.id}')" 
                            class="text-xs text-red-600 hover:text-red-800 font-bold hover:underline">
                        &times; Remove
                    </button>
                </div>
                <input type="hidden" name="new_users[${userId}][id]" value="${userId}">
                <input type="text" name="new_users[${userId}][task_description]" 
                       placeholder="Enter task for ${userName}" 
                       class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', newFieldHTML);
        selectedOption.disabled = true;
        selectElement.selectedIndex = 0;
    }

    function removeNewUserField(userId, selectElementId) {
        const entryToRemove = document.getElementById('new-user-entry-' + userId);
        if (entryToRemove) entryToRemove.remove();

        const selectElement = document.getElementById(selectElementId);
        if (selectElement) {
            const optionToEnable = selectElement.querySelector(`option[value="${userId}"]`);
            if (optionToEnable) optionToEnable.disabled = false;
        }
    }
</script>
@endpush