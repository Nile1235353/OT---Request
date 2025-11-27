<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>OT Management System</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Alpine.js Script for Toast Notification --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">

    <style>
        /* CSS Helper Classes */
        .page-card {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        /* Mobile menu open state animation */
        #mobile-menu.menu-open {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }
        /* User dropdown open state animation */
        #user-menu-dropdown.dropdown-open {
            opacity: 1;
            pointer-events: auto;
            transform: scale(1);
        }
        /* Active nav button style for mobile dropdown */
        .mobile-nav-button.active {
            background-color: #4f46e5; /* indigo-600 */
            color: white;
        }
    </style>
</head>
<body class="antialiased">

    {{-- Toast Notification Component --}}
    @if (session('success'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 4000)"
             x-show="show"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-5 right-5 z-50 max-w-sm w-full bg-green-500 shadow-lg rounded-xl pointer-events-auto">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-semibold text-white">
                            {{ session('success') }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button @click="show = false" type="button" class="inline-flex rounded-md bg-green-500 text-green-100 hover:text-white focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 focus:ring-offset-green-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="menu-overlay" class="fixed inset-0 bg-black/50 z-20 hidden"></div>

    {{-- Flex Column Layout to keep Footer at bottom --}}
    <div class="min-h-screen flex flex-col font-sans text-gray-800" style="background: linear-gradient(to top right, #eff6ff, #e0e7ff);">
        <header class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-30 relative">
            
            <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                
                <a href="/dashboard" class="text-2xl font-bold text-indigo-600 cursor-pointer">OT Management</a>

                <div class="flex items-center">
                    <div id="desktop-menu" class="hidden md:flex items-center space-x-1">
                        @auth
                            @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                <a href="/users" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('users*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">User Management</a>
                                
                                <a href="/ot-attendance" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('ot-attendance*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">
                                    Fingerprint Import
                                </a>
                            @endif
                        @endauth
                        
                        @auth
                            @if(auth()->user()->role == 'Admin' || in_array(auth()->user()->position, ['Manager']))
                                <a href="/reports/employee-ot" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('reports*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">OT Report</a>
                            @endif
                        @endauth
                        
                        <a href="/my-ot" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('my-ot*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">My OT</a>

                        @auth
                            @if(auth()->user()->role == 'Admin' || in_array(auth()->user()->position, ['Supervisor', 'Assistant Supervisor', 'Manager']))
                                <a href="/request-ot" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('request-ot*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">Request OT</a>
                            @endif
                        @endauth

                        @auth
                            @if(auth()->user()->role == 'Admin' || in_array(auth()->user()->position, ['Manager']))
                                <a href="/approve-ot" class="nav-link px-4 py-2 rounded-md text-sm font-medium {{ request()->is('approve-ot*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-indigo-100/50' }}">Approve OT</a>
                            @endif
                        @endauth
                        
                    </div>

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
            
            <div id="mobile-menu" class="absolute top-full left-0 w-full bg-white/95 backdrop-blur-md shadow-lg md:hidden opacity-0 pointer-events-none transform -translate-y-2 transition-all duration-300 ease-in-out z-40">
                @auth
                    @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                        <a href="/users" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('users*') ? 'active' : '' }}">User Management</a>
                        
                        <a href="/ot-attendance" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('ot-attendance*') ? 'active' : '' }}">
                            Fingerprint Import
                        </a>
                    @endif
                @endauth
                @auth
                    @if(auth()->user()->role == ['Admin', 'HR'] || in_array(auth()->user()->position, ['Manager']))
                        <a href="/reports/employee-ot" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('reports*') ? 'active' : '' }}">OT Report</a>
                    @endif
                @endauth 
                
                <a href="/my-ot" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('my-ot*') ? 'active' : '' }}">My OT</a>

                @auth
                    @if(auth()->user()->role == 'Admin' || in_array(auth()->user()->position, ['Supervisor', 'Assistant Supervisor', 'Manager']))
                        <a href="/request-ot" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('request-ot*') ? 'active' : '' }}">Request OT</a>
                    @endif
                @endauth
                
                @auth
                    @if(auth()->user()->role == 'Admin' || in_array(auth()->user()->position, ['Manager']))
                        <a href="/approve-ot" class="mobile-nav-button block py-2 px-4 text-sm hover:bg-gray-200 {{ request()->is('approve-ot*') ? 'active' : '' }}">Approve OT</a>
                    @endif
                @endauth
                
            </div>
        </header>

        {{-- Main Content --}}
        <main class="container mx-auto p-6 md:p-8 flex-grow">
            @yield('content')
        </main>

        {{-- [NEW] Footer Section --}}
        <footer class="bg-white/60 backdrop-blur-sm border-t border-gray-200 mt-auto">
            <div class="container mx-auto px-6 py-4 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
                <div class="mb-2 md:mb-0 text-center md:text-left">
                    {{-- Company Name & Copyright --}}
                    &copy; {{ date('Y') }} <span class="font-bold text-indigo-600">OT Management System</span>. All rights reserved.
                </div>
                <div class="flex items-center gap-4">
                    {{-- Developer Info or Company --}}
                    <div>Developed by <span class="font-semibold text-gray-700">RGL Software team</span></div>
                    {{-- Version --}}
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