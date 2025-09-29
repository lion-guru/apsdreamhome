<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center space-x-2">
                <img src="{{ asset('images/logo.png') }}" alt="APS Dream Home" class="h-10">
                <span class="text-xl font-bold text-indigo-600">APS Dream Home</span>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="{{ url('/') }}" class="text-gray-700 hover:text-indigo-600 transition">Home</a>
                <a href="{{ route('properties.index') }}" class="text-gray-700 hover:text-indigo-600 transition">Properties</a>
                <a href="{{ route('about') }}" class="text-gray-700 hover:text-indigo-600 transition">About</a>
                <a href="{{ route('contact') }}" class="text-gray-700 hover:text-indigo-600 transition">Contact</a>
                
                @auth
                    <div class="relative group">
                        <button class="flex items-center space-x-1 focus:outline-none">
                            <span class="text-gray-700">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <form method="POST" action="{{ route('logout') }}" class="block w-full text-left">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-indigo-600 border border-indigo-600 rounded-md hover:bg-indigo-50 transition">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Register</a>
                @endauth
            </nav>

            <!-- Mobile menu button -->
            <button class="md:hidden focus:outline-none" id="mobile-menu-button">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ url('/') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">Home</a>
                <a href="{{ route('properties.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">Properties</a>
                <a href="{{ route('about') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">About</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">Contact</a>
                
                @auth
                    <div class="pt-4 pb-3 border-t border-gray-200 mt-4">
                        <div class="flex items-center px-4">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Dashboard</a>
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Profile</a>
                            <form method="POST" action="{{ route('logout') }}" class="block w-full">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="pt-4 pb-3 border-t border-gray-200 mt-4 space-y-2">
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">Login</a>
                        <a href="{{ route('register') }}" class="block w-full text-center px-4 py-2 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Register</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</header>
