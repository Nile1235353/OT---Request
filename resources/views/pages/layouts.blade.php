<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>OT Management System</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">

    <style>
        .page-card {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        #mobile-menu.menu-open {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }
        #user-menu-dropdown.dropdown-open {
            opacity: 1;
            pointer-events: auto;
            transform: scale(1);
        }
        .mobile-nav-button.active {
            background-color: #4f46e5;
            color: white;
        }
    </style>
</head>
<body class="antialiased">

    @if (session('success'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 4000)"
             x-show="show"
             class="fixed top-5 right-5 z-50 max-w-sm w-full bg-green-500 shadow-lg rounded-xl pointer-events-auto">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-semibold text-white">{{ session('success') }}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button @click="show = false" type="button" class="inline-flex rounded-md bg-green-500 text-green-100 hover:text-white focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="menu-overlay" class="fixed inset-0 bg-black/50 z-20 hidden"></div>

    <div class="min-h-screen flex flex-col font-sans text-gray-800" style="background: linear-gradient(to top right, #eff6ff, #e0e7ff);">
        <header class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-30 relative">
            
            <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                
                <a href="/dashboard" class="text-2xl font-bold text-indigo-600 cursor-pointer">OT Management</a>

                <div class="flex items-center">
                    {{-- ======================== --}}
                    {{-- DESKTOP MENU --}}
                    {{-- ======================== --}}
                    <div id="desktop-menu" class="hidden md:flex items-center space-x-1">
                        @auth
                            {{-- 1. User Management & Fingerprint (Role: Admin OR HR) --}}
                            @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                <a href="/users" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('users*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">
                                    User Management
                                </a>
                                
                                <a href="/ot-attendance" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('ot-attendance*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">
                                    Fingerprint Import
                                </a>
                            @endif

                            {{-- 2. OT Report --}}
                            {{-- LOGIC: Admin OR HR OR Management (Role) OR Manager (Position) --}}
                            @if(in_array(auth()->user()->role, ['Admin', 'HR', 'Management']) || auth()->user()->position == 'Manager')
                                <a href="/reports/employee-ot" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('reports*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">
                                    OT Report
                                </a>
                            @endif
                            
                            {{-- 3. My OT --}}
                            {{-- LOGIC: Show to everyone EXCEPT 'Management' Role --}}
                            @if(auth()->user()->role !== 'Management')
                                <a href="/my-ot" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('my-ot*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">
                                    My OT
                                </a>
                            @endif

                            {{-- 4. Request OT --}}
                            {{-- LOGIC: (Admin OR Manager/Sup/Asst OR can_request_ot) AND (Role is NOT Management) --}}
                            @if((auth()->user()->role == 'Admin' || in_array(auth()->user()->position, ['Manager', 'Supervisor', 'Assistant Supervisor']) || auth()->user()->can_request_ot == 1) && auth()->user()->role !== 'Management')
                                <a href="/request-ot" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('request-ot*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">
                                    Request OT
                                </a>
                            @endif

                            {{-- 5. Approve OT --}}
                            {{-- LOGIC: (Admin OR Manager) AND (Role is NOT Management) --}}
                            @if((auth()->user()->role == 'Admin' || auth()->user()->position == 'Manager') && auth()->user()->role !== 'Management')
                                <a href="/approve-ot" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('approve-ot*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">
                                    Approve OT
                                </a>
                            @endif
                        @endauth
                    </div>

                    {{-- User Profile Dropdown --}}
                    @auth
                        <div id="user-profile-container" class="relative ml-4">
                            <button id="user-menu-button" class="flex text-sm bg-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Open user menu</span>
                                <svg class="h-8 w-8 rounded-full text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" /></svg>
                            </button>
                            <div id="user-menu-dropdown" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 pointer-events-none transform scale-95 transition-all duration-150 ease-in-out z-50">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-sm font-semibold text-gray-900">Signed in as</p>
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->name }}</p>
                                </div>
                                <div class="py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">Logout</a>
                                </div>
                            </div>
                        </div>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    @else
                        <div class="hidden md:flex items-center space-x-4 ml-4">
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">Log in</a>
                            <a href="" class="text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-md">Register</a>
                        </div>
                    @endauth

                    <div class="md:hidden flex items-center ml-2">
                        <button id="menu-btn" class="outline-none">
                            <svg class="w-6 h-6 text-gray-500 hover:text-indigo-600" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                        </button>
                    </div>
                </div>
            </nav>
            
            {{-- ======================== --}}
            {{-- MOBILE MENU --}}
            {{-- ======================== --}}
            <div id="mobile-menu" class="absolute top-full left-0 w-full bg-white/95 backdrop-blur-md shadow-lg md:hidden opacity-0 pointer-events-none transform -translate-y-2 transition-all duration-300 ease-in-out z-40">
                @auth
                    @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                        <a href="/users" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('users*') ? 'active' : '' }}">
                            User Management
                        </a>
                        <a href="/ot-attendance" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('ot-attendance*') ? 'active' : '' }}">
                            Fingerprint Import
                        </a>
                    @endif

                    @if(in_array(auth()->user()->role, ['Admin', 'HR', 'Management']) || auth()->user()->position == 'Manager')
                        <a href="/reports/employee-ot" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('reports*') ? 'active' : '' }}">
                            OT Report
                        </a>
                    @endif
                    
                    @if(auth()->user()->role !== 'Management')
                        <a href="/my-ot" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('my-ot*') ? 'active' : '' }}">
                            My OT
                        </a>
                    @endif

                    @if((auth()->user()->role == 'Admin' || in_array(auth()->user()->position, ['Manager', 'Supervisor', 'Assistant Supervisor']) || auth()->user()->can_request_ot == 1) && auth()->user()->role !== 'Management')
                        <a href="/request-ot" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('request-ot*') ? 'active' : '' }}">
                            Request OT
                        </a>
                    @endif
                    
                    @if((auth()->user()->role == 'Admin' || auth()->user()->position == 'Manager') && auth()->user()->role !== 'Management')
                        <a href="/approve-ot" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('approve-ot*') ? 'active' : '' }}">
                            Approve OT
                        </a>
                    @endif
                @endauth
            </div>
        </header>

        <main class="container mx-auto p-6 md:p-8 flex-grow">
            @yield('content')
        </main>

        <footer class="bg-white/60 backdrop-blur-sm border-t border-gray-200 mt-auto">
            <div class="container mx-auto px-6 py-4 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
                <div class="mb-2 md:mb-0 text-center md:text-left">
                    &copy; {{ date('Y') }} <span class="font-bold text-indigo-600">OT Management System</span>. All rights reserved.
                </div>
                <div class="flex items-center gap-4">
                    <div>Developed by <span class="font-semibold text-gray-700">RGL Software team</span></div>
                    <div class="font-semibold bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded border border-indigo-100">
                        Beta Version
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuBtn = document.getElementById('menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuOverlay = document.getElementById('menu-overlay');
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');
            const userProfileContainer = document.getElementById('user-profile-container');

            function openMobileMenu() {
                if(mobileMenu) mobileMenu.classList.add('menu-open');
                if(menuOverlay) menuOverlay.classList.remove('hidden');
            }
            function closeMobileMenu() {
                if(mobileMenu) mobileMenu.classList.remove('menu-open');
                if(menuOverlay) menuOverlay.classList.add('hidden');
            }
            function openUserMenu() {
                if(userMenuDropdown) userMenuDropdown.classList.add('dropdown-open');
            }
            function closeUserMenu() {
                if(userMenuDropdown) userMenuDropdown.classList.remove('dropdown-open');
            }

            if (menuBtn) {
                menuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (mobileMenu && mobileMenu.classList.contains('menu-open')) {
                        closeMobileMenu();
                    } else {
                        openMobileMenu();
                    }
                });
            }

            if (userMenuButton) {
                userMenuButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (userMenuDropdown && userMenuDropdown.classList.contains('dropdown-open')) {
                        closeUserMenu();
                    } else {
                        openUserMenu();
                    }
                });
            }
            
            window.addEventListener('click', (e) => {
                if (userProfileContainer && !userProfileContainer.contains(e.target)) {
                    closeUserMenu();
                }
                if (menuOverlay && e.target === menuOverlay) {
                    closeMobileMenu();
                }
            });
        });
    </script>

    @if (session('error'))
        <script>
            alert("{{ session('error') }}");
        </script>
    @endif

    @stack('scripts')
</body>
</html>