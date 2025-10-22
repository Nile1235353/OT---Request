<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OT Request Login</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Cormorant Garamond (Serif Display Font for Title) - Added italic styles -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&display=swap" rel="stylesheet">
    <style>
        /* Custom Styles */
        body {
            /* Default font is Poppins */
            font-family: 'Poppins', sans-serif;
            background: url("{{ asset('images/loginBackground.png') }}") no-repeat repeat; /* center fixed */
            background-size: cover;
        }
        /* Special class for the Serif Display title */
        .font-serif-display {
            font-family: 'Cormorant Garamond', serif;
        }
        /* Background effect simulation */
        .background-container {
            position: relative;
            overflow: hidden;
            
            /* TODO: Replace this placeholder URL with the actual path to your JPEG image */
            /* background-image: url('https://placehold.co/1920x1080/000c1a/FFFFFF?text=Replace+With+Your+Background+JPEG'); */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
            /* background: linear-gradient(180deg, #020024 0%, #090979 35%, #000c1a 100%); */
        }
        .background-container::before {
            content: "";
            position: absolute;
            bottom: -60vh; /* Position it lower */
            left: 50%;
            transform: translateX(-50%);
            width: 150vw; /* Make it wider */
            height: 120vh; /* Make it taller */
            /* This glow effect might interfere with your background image, you can remove it if needed */
            /* background-image: radial-gradient(circle, rgba(29, 78, 216, 0.25) 10%, rgba(29, 78, 216, 0) 60%); */
            border-radius: 50%;
            z-index: 1;
            pointer-events: none;
            /* This simulates the dotted globe effect's glow */
        }
        /* Style for the login card to be above the glow */
        .login-card {
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body class="background-container text-gray-900">

    <!-- Main Container -->
    <div class="flex items-center justify-center min-h-screen p-4">
        
        <!-- Login Card -->
        <div class="login-card bg-white w-full max-w-md p-8 sm:p-10 rounded-xl shadow-2xl">
            
            <!-- Logo -->
            <div class="text-center mb-6">
                <!-- SVG ကို PNG image tag နဲ့ အစားထိုးထားပါတယ်။ -->
                <!-- TODO: ဒီ placeholder URL နေရာမှာ သင့်ရဲ့ PNG logo file path ကို အစားထိုးပါ။ -->
                <img 
                    src="{{ asset('images/loginLogo.png') }}" 
                    alt="RGL Logo" 
                    class="w-auto h-12 mx-auto"
                >
            </div>

            <!-- Title -->
            <!-- Added italic class / Increased size from 2xl to 3xl -->
            <h1 class="text-3xl text-center text-gray-700 font-serif-display italic"><strong>OT Request Login</strong></h1>
            <!-- Added italic class / Increased size from sm to base -->
            <p class="text-base text-gray-600 text-center mt-2 mb-8 font-light font-serif-display italic">Sign in to your account to continue.</p>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    
                    <!-- Email Field -->
                    <div>
                        <!-- Kept font-semibold (600 weight) for labels -->
                        <label for="email" class="flex items-center text-sm font-semibold text-gray-800 mb-2">
                            <!-- Mail Icon SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-500 mr-2">
                                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                <polyline points="3 7 12 13 21 7"></polyline>
                            </svg>
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email"
                            value="lmsaccount@gmail.com"
                            class="w-full px-4 py-3 bg-slate-200 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition duration-300"
                            required
                        >
                    </div>
                    
                    <!-- Password Field -->
                    <div>
                        <!-- Kept font-semibold (600 weight) for labels -->
                        <label for="password" class="flex items-center text-sm font-semibold text-gray-800 mb-2">
                            <!-- Lock Icon SVG -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-500 mr-2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5
                                0 0 1 10 0v4"></path>
                            </svg>
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            class="w-full px-4 py-3 bg-slate-200 text-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition duration-300"
                            required
                        >
                    </div>

                </div>

                <!-- Forgot Password Link -->
                <div class="text-right mt-3">
                    <a href="#" class="text-xs text-blue-600 hover:underline">Forgot password?</a>
                </div>

                <!-- Sign In Button -->
                <button 
                    type="submit"
                    class="w-full bg-blue-800 text-white p-3 rounded-lg font-semibold hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition duration-300 mt-8"
                >
                    Sign In
                </button>

                <!-- Sign Up Link -->
                <p class="text-center text-sm text-gray-500 mt-8">
                    Don't have an account? 
                    <a href="#" class="font-medium text-blue-600 hover:underline">Sign up</a>
                </p>

            </form>
        </div>
    </div>

</body>
</html>



