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

    /* 1. Edit Modal Specifics 
       (အပေါ်ကနေ နည်းနည်းခွာပြီး ပေါ်မယ် - Scroll လုပ်လို့ရမယ်)
    */
    .edit-modal-overlay {
        align-items: flex-start; 
        padding-top: 5vh;
        justify-content: center;
    }

    /* 2. Reject Modal Specifics 
       (အလယ်တည့်တည့်မှာ ပေါ်မယ်)
    */
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
        <h3 class="text-2xl font-bold mb-6 border-b pb-3">Pending OT Approvals</h3>
        <div class="space-y-4">
            @forelse($pendingRequests as $request)
                <div class="border border-gray-200 p-4 rounded-lg bg-white grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <div class="md:col-span-3 space-y-3">
                        <p><span class="font-bold">Date:</span> {{ \Carbon\Carbon::parse($request->ot_date)->format('m/d/Y') }}</p>
                        <p><span class="font-bold">OT ID:</span> {{ $request->request_id }}</p>
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
                        {{-- Edit & Approve Button --}}
                        <button type="button" 
                                onclick="openEditModal('editModal-{{ $request->id }}')" 
                                class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Edit & Approve
                        </button>
                        
                        {{-- [UPDATED] Reject Button (Opens Shared Modal) --}}
                        <button type="button" 
                                onclick="openRejectModal('{{ $request->id }}')" 
                                class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                            Reject
                        </button>
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
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        {{-- Added Remark Column --}}
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reject Remark</th>
                        <th class="table-header px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($historyRequests as $history)
                    <tr>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($history->ot_date)->format('m/d/Y') }}</td>
                        <td class="px-6 py-4">{{ $history->supervisor->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $history->total_hours }}</td>
                        <td class="px-6 py-4">
                            @if($history->status == 'approved')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                            @elseif($history->status == 'rejected')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                            @endif
                        </td>
                        
                        {{-- Reject Remark Display --}}
                        <td class="px-6 py-4 text-sm text-gray-500 italic">
                            {{ $history->reject_remark ?? '-' }}
                        </td>

                        <td class="px-6 py-4">
                            @if($history->status == 'approved')
                                {{-- Reverse/Reject Button --}}
                                <button type="button" 
                                        onclick="openRejectModal('{{ $history->id }}')" 
                                        class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                                    Reject
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-6 text-gray-500">No history found.</td></tr>
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
    {{-- Added class 'edit-modal-overlay' --}}
    <div id="editModal-{{ $request->id }}" class="modal-overlay edit-modal-overlay" onclick="closeModal('editModal-{{ $request->id }}')">
        <div class="modal-content edit-modal-content" onclick="event.stopPropagation();">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h4 class="text-xl font-bold text-indigo-700">Edit & Approve OT Request</h4>
                <button onclick="closeModal('editModal-{{ $request->id }}')" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                <p><span class="font-bold">Title:</span> {{ $request->title }}</p>
                <p><span class="font-bold">Date:</span> {{ \Carbon\Carbon::parse($request->ot_date)->format('m/d/Y') }}</p>
            </div>

            <form action="{{ route('approvals.approve', $request->id) }}" method="POST">
                @csrf
                
                <h5 class="font-bold mb-2 text-gray-700">Current Team (Edit Tasks / Remove):</h5>
                <div class="space-y-3 mb-6 max-h-[30vh] overflow-y-auto pr-2 border border-gray-100 rounded p-2">
                    @foreach($request->assignedUsers as $assignedUser)
                        <div class="border p-3 rounded-md bg-gray-50">
                            <label class="font-semibold text-gray-800">{{ $assignedUser->user->name ?? 'N/A' }}</label>
                            <input type="hidden" name="users[{{ $assignedUser->user->id }}][id]" value="{{ $assignedUser->user->id }}">
                            
                            <label class="block text-xs font-medium text-gray-500 mt-2">Task:</label>
                            <input type="text" name="users[{{ $assignedUser->user->id }}][task_description]" 
                                   value="{{ $assignedUser->task_description }}" 
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm mt-1">
                            
                            <div class="mt-2">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="users[{{ $assignedUser->user->id }}][remove]" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-red-600 hover:text-red-800 font-medium">Remove user</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <h5 class="font-bold mb-2 text-gray-700">Add New User:</h5>
                <div class="border p-3 rounded-md bg-blue-50 mb-6 space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Select User:</label>
                    <select id="newUserSelect-{{ $request->id }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" 
                            onchange="addNewUserField(this, 'new-users-container-{{ $request->id }}')">
                        <option value="">-- Select a user to add --</option>
                        @if(isset($allUsers))
                            @foreach($allUsers as $user)
                                @if(!$request->assignedUsers->pluck('user_id')->contains($user->id))
                                    <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                @endif
                            @endforeach
                        @else
                            <option value="" disabled>User list not available</option>
                        @endif
                    </select>
                    <div id="new-users-container-{{ $request->id }}" class="space-y-3 mt-3"></div>
                </div>
                
                <div class="flex justify-end space-x-3 border-t pt-4">
                    <button type="button" onclick="closeModal('editModal-{{ $request->id }}')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-md hover:bg-indigo-700 shadow-sm">
                        Approve with Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- ================================================================= --}}
{{-- 2. SHARED REJECT MODAL --}}
{{-- ================================================================= --}}
{{-- Added class 'reject-modal-overlay' for centering --}}
<div id="sharedRejectModal" class="modal-overlay reject-modal-overlay" onclick="closeRejectModal()">
    <div class="modal-content reject-modal-content" onclick="event.stopPropagation();">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h4 class="text-xl font-bold text-red-600">Reject OT Request</h4>
            <button onclick="closeRejectModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
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
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-md hover:bg-red-700 shadow-sm">
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

    // --- Dynamic User Field Functions (From your snippet) ---

    function addNewUserField(selectElement, containerId) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const userId = selectedOption.value;
        const userName = selectedOption.dataset.name;

        if (!userId) return;

        const container = document.getElementById(containerId);
        if (document.getElementById('new-user-entry-' + userId)) return;

        const newFieldHTML = `
            <div id="new-user-entry-${userId}" class="new-user-entry" data-user-id="${userId}">
                <div class="flex justify-between items-center">
                    <label class="font-semibold text-indigo-700">${userName}</label>
                    <button type="button" onclick="removeNewUserField('${userId}', '${selectElement.id}')" 
                            class="text-xs text-red-600 hover:underline font-bold">
                        &times; Remove
                    </button>
                </div>
                <input type="hidden" name="new_users[${userId}][id]" value="${userId}">
                <label class="block text-xs font-medium text-gray-500 mt-1">Task:</label>
                <input type="text" name="new_users[${userId}][task_description]" 
                       placeholder="Task for ${userName}" 
                       class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
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