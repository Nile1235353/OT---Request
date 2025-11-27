<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - OT Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Animated Gradient Background for Left Side */
        .animated-bg {
            background: linear-gradient(-45deg, #4f46e5, #3b82f6, #06b6d4, #6366f1);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="h-full">
    <div class="flex min-h-full">
        
        {{-- LEFT SIDE: Form Section --}}
        <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-white z-10">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                
                {{-- Branding / Logo --}}
                <div>
                    {{-- Replace src with your actual logo path --}}
                    {{-- <img class="h-12 w-auto" src="{{ asset('images/logo.png') }}" alt="Your Company"> --}}
                    
                    {{-- Text Logo Fallback --}}
                    <div class="flex items-center gap-2">
                        <div class="h-10 w-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                            OT
                        </div>
                        <span class="text-2xl font-bold text-gray-900 tracking-tight">Management</span>
                    </div>

                    <h2 class="mt-8 text-2xl font-bold leading-9 tracking-tight text-gray-900">
                        Welcome back!
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Please sign in to access your dashboard.
                    </p>
                </div>

                <div class="mt-10">
                    <div class="mt-10">
                        <form action="{{ route('login') }}" method="POST" class="space-y-6">
                            @csrf
                            
                            {{-- Email Input --}}
                            <div>
                                <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                                <div class="mt-2">
                                    <input id="email" name="email" type="email" autocomplete="email" required 
                                        class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition duration-150 ease-in-out"
                                        placeholder="admin@example.com"
                                        value="{{ old('email') }}">
                                </div>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Password Input --}}
                            <div>
                                <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                                <div class="mt-2 relative">
                                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                                        class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition duration-150 ease-in-out">
                                </div>
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Remember Me & Forgot Password --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember-me" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="remember-me" class="ml-3 block text-sm leading-6 text-gray-700">Remember me</label>
                                </div>

                                <div class="text-sm leading-6">
                                    <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500 transition">Forgot password?</a>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div>
                                <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition duration-200 ease-in-out transform hover:scale-[1.01]">
                                    Sign in
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Sign Up Link (Optional) --}}
                    <p class="mt-10 text-center text-sm text-gray-500">
                        Don't have an account?
                        <a href="#" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500 transition">Contact HR</a>
                    </p>
                </div>
            </div>
        </div>

        {{-- RIGHT SIDE: Image/Visual Section --}}
        <div class="relative hidden w-0 flex-1 lg:block animated-bg">
            {{-- Optional: You can put an actual image here instead of the gradient --}}
            {{-- <img class="absolute inset-0 h-full w-full object-cover" src="{{ asset('images/login-bg.jpg') }}" alt=""> --}}
            
            {{-- Decorative Pattern --}}
            <div class="absolute inset-0 flex items-center justify-center text-white opacity-90">
                <div class="text-center px-8">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl mb-6">Manage Overtime Efficiently</h1>
                    <p class="text-lg text-indigo-100 max-w-lg mx-auto">
                        Streamline your workflow, track attendance with precision, and manage approvals seamlessly in one place.
                    </p>
                    
                    {{-- Decorative floating elements --}}
                    <div class="absolute top-1/4 left-1/4 w-24 h-24 bg-white rounded-full opacity-10 blur-2xl animate-pulse"></div>
                    <div class="absolute bottom-1/3 right-1/4 w-32 h-32 bg-indigo-400 rounded-full opacity-20 blur-3xl animate-bounce" style="animation-duration: 8s;"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>