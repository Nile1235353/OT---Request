@extends('pages.layouts')

@section('content')

{{-- MODAL STYLES --}}
<style>
    /* Modal Overlay */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.6);
        display: none; /* Hidden by default */
        /* align-items: center; */ /* CHANGED: Center အစား အပေါ်မှာ ပေါ်စေရန် */
        align-items: flex-start; /* CHANGED */
        padding-top: 10vh; /* ADDED: အပေါ်ကနေ နည်းနည်းခြားပေးရန် */
        justify-content: center;
        z-index: 999;
        /* Add scroll if content is too tall on small screens */
        overflow-y: auto; 
    }
    /* Modal Content */
    .modal-content {
        background-color: white;
        padding: 1.5rem 2rem;
        border-radius: 0.5rem;
        z-index: 1000;
        width: 90%;
        max-width: 600px; /* Max width for the modal */
        /* max-height: 85vh; */ /* Removed max-height, handled by overlay scroll */
        /* overflow-y: auto; */
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        margin-bottom: 5vh; /* Add some space at the bottom when scrolling */
    }

    /* --- ADDED: Style for new user entry in modal --- */
    .new-user-entry {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        border: 1px solid #e0e0e0;
        padding: 0.75rem;
        border-radius: 0.375rem;
        background-color: #f9fafb; /* bg-gray-50 */
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
                            {{-- MODIFIED: Changed Approve form to a button to open modal --}}
                            <button type="button" 
                                    onclick="openModal('editModal-{{ $request->id }}')" 
                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                Edit & Approve
                            </button>
                            
                            {{-- Kept Reject form as is --}}
                            <form action="{{ route('approvals.reject', $request->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Reject</button>
                            </form>
                        </div>

                        {{-- MOVED: Modal div ကို grid layout အပြင်ကို ရွှေ့လိုက်ပါပြီ။ --}}
                        {{-- Grid item div (အပေါ်က </div>) ပိတ်ပြီးမှ modal ကို ထားပါတယ်။ --}}
                    </div>

                    {{-- REMOVED: Modal HTML block ကို ဒီနေရာကနေ ဖယ်ရှားပြီး အောက်ဆုံးကို ရွှေ့လိုက်ပါပြီ။ --}}
                    
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
                            <th class="table-header">Action</th> {{-- ADDED: Action column --}}
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
                            {{-- ADDED: Action column content --}}
                            <td class="px-6 py-4">
                                @if($history->status == 'approved')
                                    {{-- ADDED: Reverse/Reject button for approved items --}}
                                    <form action="{{ route('approvals.reject', $history->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to REVERSE this approval and REJECT it?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                                            Reject
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            {{-- MODIFIED: Colspan 6 (Action column added) --}}
                            <td colspan="6" class="text-center py-6 text-gray-500">No history found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ADDED: Modals Section --}}
    {{-- This loops through the pending requests again to generate the modals --}}
    {{-- This places them at the end of the DOM, outside the main layout flow --}}
    @foreach($pendingRequests as $request)
        <div id="editModal-{{ $request->id }}" class="modal-overlay" onclick="closeModal('editModal-{{ $request->id }}')">
            <div class="modal-content" onclick="event.stopPropagation();">
                <div class="flex justify-between items-center mb-4">
                        <h4 class="text-xl font-bold">Edit & Approve OT Request</h4>
                        <button onclick="closeModal('editModal-{{ $request->id }}')" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
                    </div>
                    <p class="mb-2"><span class="font-bold">Title:</span> {{ $request->title }}</p>
                    <p class="mb-4"><span class="font-bold">Date:</span> {{ \Carbon\Carbon::parse($request->ot_date)->format('m/d/Y') }}</p>

                    {{-- New form that submits to the approve route --}}
                    <form action="{{ route('approvals.approve', $request->id) }}" method="POST">
                        @csrf
                        
                        <h5 class="font-bold mb-2">Current Team (Edit Tasks / Remove):</h5>
                        <div class="space-y-3 mb-4 max-h-[30vh] overflow-y-auto pr-2"> {{-- Added max-height and scroll for user list --}}
                            @foreach($request->assignedUsers as $assignedUser)
                                <div class="border p-3 rounded-md bg-gray-50">
                                    <label class="font-semibold">{{ $assignedUser->user->name ?? 'N/A' }}</slabel>
                                    
                                    {{-- We pass the user ID so the controller knows *who* is being edited --}}
                                    <input type="hidden" name="users[{{ $assignedUser->user->id }}][id]" value="{{ $assignedUser->user->id }}">
                                    
                                    <label class="block text-sm font-medium text-gray-700 mt-1">Task:</label>
                                    <input type="text" name="users[{{ $assignedUser->user->id }}][task_description]" 
                                           value="{{ $assignedUser->task_description }}" 
                                           class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                           
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="users[{{ $assignedUser->user->id }}][remove]" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                                            <span class="ml-2 text-sm text-red-700">Remove this user</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- === MODIFIED: Add New User Section === --}}
                        <h5 class="font-bold mb-2">Add New User:</h5>
                        <div class="border p-3 rounded-md bg-blue-50/50 mb-6 space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Select User:</label>
                            {{-- Use a unique ID for each modal's select box --}}
                            <select id="newUserSelect-{{ $request->id }}" class="w-full border-gray-300 rounded-md shadow-sm text-sm" 
                                    onchange="addNewUserField(this, 'new-users-container-{{ $request->id }}')">
                                <option value="">-- Select a user to add --</option>
                                @if(isset($allUsers))
                                    @foreach($allUsers as $user)
                                        {{-- Prevent adding user who is already in the list --}}
                                        @if(!$request->assignedUsers->pluck('user_id')->contains($user->id))
                                            <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                @else
                                    <option value="" disabled>User list not available</option>
                                @endif
                            </select>
                            
                            {{-- This container will hold the dynamically added user fields --}}
                            <div id="new-users-container-{{ $request->id }}" class="space-y-3 mt-3">
                                {{-- New user fields will be appended here by JS --}}
                            </div>
                        </div>
                        
                        <!-- Modal Action Buttons -->
                        <div class="flex justify-end space-x-3 border-t pt-4">
                            <button type="button" 
                                    onclick="closeModal('editModal-{{ $request->id }}')"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                                Approve with Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    @endforeach
    {{-- END OF NEW SECTION --}}


{{-- MODAL SCRIPT --}}
<script>
    function openModal(modalId) {
        // Use flex display to align center
        document.getElementById(modalId).style.display = 'flex';
        // Prevent background scrolling
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        // Allow background scrolling again
        document.body.style.overflow = 'auto';
    }

    // --- ADDED: Dynamic User Field Functions ---

    /**
     * Called when a user is selected from the dropdown.
     * Dynamically adds a new block for task input.
     */
    function addNewUserField(selectElement, containerId) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const userId = selectedOption.value;
        const userName = selectedOption.dataset.name;

        if (!userId) {
            return; // Do nothing if "-- Select --" is re-selected
        }

        const container = document.getElementById(containerId);

        // Check if user is already added (to prevent double-adding if logic is slow)
        if (document.getElementById('new-user-entry-' + userId)) {
            return;
        }

        // Create the new HTML block
        const newFieldHTML = `
            <div id="new-user-entry-${userId}" class="new-user-entry" data-user-id="${userId}">
                <div class="flex justify-between items-center">
                    <label class="font-semibold">${userName}</label>
                    <button type="button" onclick="removeNewUserField('${userId}', '${selectElement.id}')" 
                            class="text-sm text-red-600 hover:underline">
                        Remove
                    </button>
                </div>
                <input type="hidden" name="new_users[${userId}][id]" value="${userId}">
                <label class="block text-sm font-medium text-gray-700 mt-1">Task:</label>
                <input type="text" name="new_users[${userId}][task_description]" 
                       placeholder="Task for ${userName}" 
                       class="w-full border-gray-300 rounded-md shadow-sm text-sm" required>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', newFieldHTML);

        // Disable the option in the dropdown to prevent re-adding
        selectedOption.disabled = true;
        // Reset dropdown to default
        selectElement.selectedIndex = 0;
    }

    /**
     * Called by the "Remove" button on a dynamically added user.
     * Removes the task block and re-enables the user in the dropdown.
     */
    function removeNewUserField(userId, selectElementId) {
        // Remove the HTML block
        const entryToRemove = document.getElementById('new-user-entry-' + userId);
        if (entryToRemove) {
            entryToRemove.remove();
        }

        // Re-enable the option in the dropdown
        const selectElement = document.getElementById(selectElementId);
        if (selectElement) {
            const optionToEnable = selectElement.querySelector(`option[value="${userId}"]`);
            if (optionToEnable) {
                optionToEnable.disabled = false;
            }
        }
    }

</script>

@endsection

