@extends('pages.layouts')

@section('content')
    {{-- Main Title From Image --}}
    <!-- <h2 class="text-3xl font-bold mb-8 text-gray-800">1. User Management Page Design</h2> -->

    {{-- Main Grid Container for two columns --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        {{-- Left Column: Create User Form (Updated Style) --}}
        <div class="md:col-span-1">
            <div class="page-card">
                <h3 class="text-xl font-bold mb-4 border-b pb-2">Create New User</h3>
                {{-- Display Success Message --}}
                @if (session('success'))
                    <div class="mt-4 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Display Validation Errors --}}
                @if ($errors->any())
                    <div class="mt-4 rounded-md bg-red-50 p-4">
                        <div class="font-medium text-red-700">Whoops! Something went wrong.</div>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- User Creation Form --}}
                <form class="space-y-5 mt-6" method="POST" action="{{ route('users.store') }}">
                    @csrf
                    {{-- Form Fields --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-800">User Name</label>
                        <input type="text" id="name" name="name"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400"
                            placeholder="e.g., U Ba">
                    </div>
                    <div>
                        <label for="employee_id" class="block text-sm font-semibold text-gray-800">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400"
                            placeholder="e.g., EMP001">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-800">Email</label>
                        <input type="text" id="email" name="email"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400"
                            placeholder="e.g., example@gmail.com">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-800">Phone Number</label>
                        <input type="text" id="phone" name="phone"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4 placeholder:text-gray-400"
                            placeholder="e.g., 09xxxxxxxxx">
                    </div>

                    {{-- UPDATED DEPARTMENT FIELD --}}
                    <div>
                        <label for="department" class="block text-sm font-semibold text-gray-800">Department</label>
                        <select id="department" name="department" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4">
                            <option value="" disabled selected>-- Select a Department --</option>
                            @foreach($departments as $department)
                                <option value="{{ $department }}">{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- UPDATED POSITION FIELD --}}
                    <div>
                        <label for="position" class="block text-sm font-semibold text-gray-800">Position</label>
                        <select id="position" name="position" class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4">
                            <option value="" disabled selected>-- Select a Position --</option>
                            @foreach($positions as $position)
                                <option value="{{ $position }}">{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-800">Role</label>
                        <select id="role" name="role"
                                class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4">
                            {{-- Login ဝင်ထားတဲ့ user ရဲ့ role က 'Admin' ဖြစ်မှ ဒီ option ကိုပြပါမယ် --}}
                            @if(auth()->user()->role == 'Admin')
                                <option>Admin</option>
                            @endif
                            <option>HR</option>
                            <option>User</option>
                        </select>
                    </div>
                    {{-- Password Fields --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-800">Password</label>
                        <input type="password" id="password" name="password"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4"
                            required>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-800">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="mt-1 block w-full rounded-lg border-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 py-2.5 px-4"
                            required>
                    </div>
                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-semibold">Create User</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Column: User List Table (Unchanged) --}}
        <div class="md:col-span-2">
            <div class="page-card">
                <h3 class="text-xl font-bold mb-4 border-b pb-2">User List</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-header">Name</th>
                                <th class="table-header">Employee ID</th>
                                <th class="table-header">Email</th>
                                <th class="table-header">Phone</th>
                                <th class="table-header">Department</th>
                                <th class="table-header">Position</th>
                                <th class="table-header">Role</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($users as $user)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{$user->name}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->employee_id}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->email}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->phone}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->department}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->position}}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{$user->role}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection