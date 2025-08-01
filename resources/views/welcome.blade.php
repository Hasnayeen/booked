<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelBook - Hotel & Bus Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @filamentStyles
    @vite(['resources/css/app.css'])
</head>
<body class="font-inter">
    <div class="fixed inset-0 -z-10">
        <img src="https://images.unsplash.com/photo-1747021627291-d81636d6f6ce?q=80&w=1932&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
            alt="Mountain landscape" 
            class="w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-blue-600/30"></div>
    </div>

    <!-- Header -->
    <header class="relative z-50">
        <div class="bg-violet-500/50 text-violet-950 text-center py-2 text-sm">
            Safe Travel Guidelines
        </div>
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="flex justify-between items-center h-16 sticky top-0">
                <div class="flex items-center space-x-2">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('logo.svg') }}" alt="" class="h-6 w-auto">
                        <h1 class="text-2xl font-bold text-violet-700">BOOKED</h1>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#" class="text-gray-700 hover:text-violet-500 text-sm font-medium">Help Center</a>
                    <div class="flex items-center space-x-2 text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="text-sm font-medium">1-800-891-2256</span>
                    </div>
                    <div class="flex items-center space-x-2 bg-gray-100 rounded-lg px-3 py-2">
                        <span class="text-xs">🇺🇸</span>
                        <span class="text-sm font-medium">USD</span>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section with Mountain Background -->
    <section class="relative min-h-screen overflow-hidden">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center min-h-[80vh]">
                <!-- Left Content -->
                <div class="text-white">
                    <div class="inline-block bg-violet-500 text-white px-4 py-2 rounded-full text-sm font-semibold mb-6">
                        2025 SEASON
                    </div>
                    <h1 class="text-5xl md:text-6xl font-bold leading-tight mb-6">
                        <span class="text-black">Hassle free booking</span><br>
                        <span class="text-black">at your fingertips</span>
                    </h1>
                    <p class="text-xl text-gray-700 mb-8 leading-relaxed max-w-lg">
                        Search hotels, book bus tickets, and plan your perfect journey all in one place
                    </p>
                    
                    <!-- Trust Badge -->
                    <div class="inline-flex items-center gap-3 bg-white rounded-lg px-4 py-3 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-200">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-[#00B67A] rounded flex items-center justify-center">
                                <span class="text-white text-xs font-bold">T</span>
                            </div>
                            <span class="text-sm font-medium text-gray-600">Trustpilot</span>
                        </div>
                        <div class="w-px h-6 bg-gray-200"></div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-0.5">
                                @for ($i=0; $i < 5; $i++)
                                    <x-lucide-star class="w-4 h-4 fill-[#00B67A] text-[#00B67A]"/>
                                @endfor
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-900">Excellent</span>
                                <span class="text-xs text-gray-500">4.9/5 • 12,847 reviews</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Booking Form -->
                @livewire('home-page-search')
            </div>
        </div>
    </section>

    <!-- Popular Destinations Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h3 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight">
                        Most Popular<br>
                        Destinations
                    </h3>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="scroll-left" class="w-12 h-12 bg-white rounded-full shadow-md flex items-center justify-center hover:shadow-lg transition-shadow duration-200">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button id="scroll-right" class="w-12 h-12 bg-white rounded-full shadow-md flex items-center justify-center hover:shadow-lg transition-shadow duration-200">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div id="destinations-container" class="flex space-x-6 overflow-x-auto scrollbar-hide pb-4" style="scroll-behavior: smooth;">
                <!-- New York Card -->
                <div class="group relative h-80 w-80 flex-shrink-0 rounded-2xl overflow-hidden cursor-pointer transform hover:scale-105 transition-all duration-300">
                    <img src="/placeholder.svg?height=400&width=600&text=New+York+Skyline" 
                        alt="New York City" 
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/20 backdrop-blur-sm text-white text-sm font-medium px-3 py-1 rounded-full">
                            631 HOTELS
                        </span>
                    </div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <h4 class="text-white text-2xl font-bold mb-2">New York, NY</h4>
                    </div>
                </div>

                <!-- San Francisco Card -->
                <div class="group relative h-80 w-80 flex-shrink-0 rounded-2xl overflow-hidden cursor-pointer transform hover:scale-105 transition-all duration-300">
                    <img src="/placeholder.svg?height=400&width=600&text=San+Francisco+Bridge" 
                        alt="San Francisco" 
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/20 backdrop-blur-sm text-white text-sm font-medium px-3 py-1 rounded-full">
                            325 HOTELS
                        </span>
                    </div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <h4 class="text-white text-2xl font-bold mb-4">San Francisco, CA</h4>
                        <button class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-2 rounded-lg transition-colors duration-200">
                            Book Now
                        </button>
                    </div>
                </div>

                <!-- Orlando Card -->
                <div class="group relative h-80 w-80 flex-shrink-0 rounded-2xl overflow-hidden cursor-pointer transform hover:scale-105 transition-all duration-300">
                    <img src="/placeholder.svg?height=400&width=600&text=Orlando+Sunset" 
                        alt="Orlando" 
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/20 backdrop-blur-sm text-white text-sm font-medium px-3 py-1 rounded-full">
                            423 HOTELS
                        </span>
                    </div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <h4 class="text-white text-2xl font-bold mb-2">Orlando, FL</h4>
                    </div>
                </div>

                <!-- Los Angeles Card -->
                <div class="group relative h-80 w-80 flex-shrink-0 rounded-2xl overflow-hidden cursor-pointer transform hover:scale-105 transition-all duration-300">
                    <img src="/placeholder.svg?height=400&width=600&text=Los+Angeles+Skyline" 
                        alt="Los Angeles" 
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/20 backdrop-blur-sm text-white text-sm font-medium px-3 py-1 rounded-full">
                            512 HOTELS
                        </span>
                    </div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <h4 class="text-white text-2xl font-bold mb-2">Los Angeles, CA</h4>
                    </div>
                </div>

                <!-- Miami Card -->
                <div class="group relative h-80 w-80 flex-shrink-0 rounded-2xl overflow-hidden cursor-pointer transform hover:scale-105 transition-all duration-300">
                    <img src="/placeholder.svg?height=400&width=600&text=Miami+Beach" 
                        alt="Miami" 
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/20 backdrop-blur-sm text-white text-sm font-medium px-3 py-1 rounded-full">
                            287 HOTELS
                        </span>
                    </div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <h4 class="text-white text-2xl font-bold mb-2">Miami, FL</h4>
                    </div>
                </div>

                <!-- Las Vegas Card -->
                <div class="group relative h-80 w-80 flex-shrink-0 rounded-2xl overflow-hidden cursor-pointer transform hover:scale-105 transition-all duration-300">
                    <img src="/placeholder.svg?height=400&width=600&text=Las+Vegas+Strip" 
                        alt="Las Vegas" 
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/20 backdrop-blur-sm text-white text-sm font-medium px-3 py-1 rounded-full">
                            398 HOTELS
                        </span>
                    </div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <h4 class="text-white text-2xl font-bold mb-4">Las Vegas, NV</h4>
                        <button class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-2 rounded-lg transition-colors duration-200">
                            Book Now
                        </button>
                    </div>
                </div>

                <!-- Chicago Card -->
                <div class="group relative h-80 w-80 flex-shrink-0 rounded-2xl overflow-hidden cursor-pointer transform hover:scale-105 transition-all duration-300">
                    <img src="/placeholder.svg?height=400&width=600&text=Chicago+Skyline" 
                        alt="Chicago" 
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/20 backdrop-blur-sm text-white text-sm font-medium px-3 py-1 rounded-full">
                            456 HOTELS
                        </span>
                    </div>
                    <div class="absolute bottom-4 left-4 right-4">
                        <h4 class="text-white text-2xl font-bold mb-2">Chicago, IL</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reservations Center Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Left Image -->
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1504150558240-0b4fd8946624?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MjJ8fGJvb2tpbmd8ZW58MHx8MHx8fDA%3D" 
                        alt="Customer service representative helping with bookings" 
                        class="w-full h-auto rounded-2xl shadow-lg">
                </div>
                
                <!-- Right Content -->
                <div>
                    <h3 class="text-4xl md:text-5xl font-bold text-gray-900 mb-12">
                        Reservations Center
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- The Best Deals -->
                        <div class="flex flex-col">
                            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">The Best Deals</h4>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                Get the best deals and cheapest prices for big savings on hotels worldwide.
                            </p>
                        </div>
                        
                        <!-- 24 HR Customer Care -->
                        <div class="flex flex-col">
                            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 109.75 9.75A9.75 9.75 0 0012 2.25z"></path>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">24 HR Customer Care</h4>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                Dedicated staff are always available to help 24 hours a day, 7 days a week.
                            </p>
                        </div>
                        
                        <!-- Biggest & Best Selection -->
                        <div class="flex flex-col">
                            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Biggest & Best Selection</h4>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                Choose from a vast array of more than 500,000 hotels and destinations.
                            </p>
                        </div>
                        
                        <!-- Secure & Simple -->
                        <div class="flex flex-col">
                            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Secure & Simple</h4>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                Book in minutes with our fast, easy, & completely secure checkout.
                            </p>
                        </div>
                        
                        <!-- Immediate Booking -->
                        <div class="flex flex-col">
                            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Immediate Booking</h4>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                Instant confirmation of reservations allows you to book now, and be done.
                            </p>
                        </div>
                        
                        <!-- Travel Insights -->
                        <div class="flex flex-col">
                            <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Travel Insights</h4>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                Helpful, reviews, advice, & pro tips from fellow travelers who have been there.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Special Offers Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-12">
                <h3 class="text-4xl md:text-5xl font-bold text-gray-900">Special Offers</h3>
                <div class="flex items-center space-x-4">
                    <button class="px-6 py-2 bg-violet-500 text-white rounded-full text-sm font-medium hover:bg-pink-500 transition-colors duration-200">
                        All
                    </button>
                    <button class="px-6 py-2 bg-white text-gray-600 rounded-full text-sm font-medium hover:bg-gray-100 transition-colors duration-200">
                        Hotels
                    </button>
                    <button class="px-6 py-2 bg-white text-gray-600 rounded-full text-sm font-medium hover:bg-gray-100 transition-colors duration-200">
                        Transport
                    </button>
                </div>
            </div>
            
            <!-- Top Row - 3 Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Hotel Discount Card -->
                <div class="bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-2xl p-8 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-white rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-4">Get up to 25% off on selected hotels</h4>
                        <button class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <span>Learn More</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Bus Routes Card -->
                <div class="bg-gradient-to-br from-violet-600 to-purple-700 rounded-2xl p-8 relative overflow-hidden">
                    <div class="absolute inset-0 bg-black/20"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-white/30 rounded-lg flex items-center justify-center mb-6">
                            <x-lucide-bus class="w-8 h-8 text-white" />
                        </div>
                        <h4 class="text-xl font-bold text-white mb-4">Get up to 20% discount on Return Tickets</h4>
                        <button class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <span>Learn More</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Travel Package Card -->
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-8 relative overflow-hidden">
                    <div class="absolute inset-0 bg-black/20"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-white mb-4">Get up to 30% discount on Travel Packages</h4>
                        <button class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-6 py-3 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <span>Learn More</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Row - 2 Wide Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Loyalty Program Banner -->
                <div class="bg-gradient-to-r from-violet-300 to-purple-700 rounded-2xl p-8 relative overflow-hidden">
                    <div class="absolute inset-0 bg-black/20"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <div class="text-violet-100 text-sm font-semibold mb-2">Welcome to</div>
                            <h4 class="text-3xl font-bold text-white mb-2">Club Primera</h4>
                            <p class="text-white/80 mb-6">Exclusive benefits and rewards for frequent travelers</p>
                            <button class="text-violet-100 bg-violet-950 hover:bg-violet-900 font-semibold px-6 py-3 rounded-lg transition-colors duration-200">
                                Learn More
                            </button>
                        </div>
                        <div class="hidden md:block">
                            <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gift Cards Banner -->
                <div class="bg-gradient-to-r from-indigo-50 to-violet-300 rounded-2xl p-8 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-violet-500 text-sm font-semibold mb-2">INTRODUCING</div>
                            <h4 class="text-3xl font-bold text-gray-900 mb-2">GIFT CARDS</h4>
                            <p class="text-gray-600 mb-6">Gift experiences that last a lifetime</p>
                            <button class="bg-pink-500 hover:bg-pink-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200">
                                Learn More
                            </button>
                        </div>
                        <div class="hidden md:block">
                            <img src="https://images.unsplash.com/photo-1649359569078-c445b3c6a116?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
                                alt="Gift cards" 
                                class="h-40 w-40 object-cover rounded-lg">
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-16 bg-violet-950">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <h3 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Never Miss a Deal
                </h3>
                <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                    Subscribe to our newsletter and be the first to know about exclusive offers, travel tips, and destination guides.
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                <input 
                    type="email" 
                    placeholder="Enter your email address" 
                    class="flex-1 px-6 py-4 rounded-lg border-0 bg-violet-50 focus:ring-2 focus:ring-violet-400 focus:outline-none text-violet-950"
                >
                <button class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-8 py-4 rounded-lg transition-colors duration-200 whitespace-nowrap">
                    Subscribe Now
                </button>
            </div>
            
            <p class="text-blue-200 text-sm mt-4">
                No spam, unsubscribe at any time. We respect your privacy.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                <!-- Company Info -->
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-2 mb-6">
                        <img src="{{ asset('logo.svg') }}" alt="" class="h-6 w-auto">
                        <h2 class="text-2xl font-bold text-violet-200">BOOKED</h2>
                    </div>
                    <p class="text-gray-300 mb-6 max-w-md">
                        Your trusted partner for seamless travel experiences. Book hotels, bus tickets, and create unforgettable memories with confidence.
                    </p>
                    
                    <!-- Contact Info -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-gray-300">1-800-891-2256</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-300">support@travelbook.com</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-gray-300">123 Travel Street, NY 10001</span>
                        </div>
                    </div>
                </div>
                
                <!-- Services -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Services</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Hotel Booking</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Bus Tickets</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Car Rentals</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Travel Packages</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Group Bookings</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Corporate Travel</a></li>
                    </ul>
                </div>
                
                <!-- Destinations -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Top Destinations</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">New York</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Los Angeles</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Miami</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Las Vegas</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Chicago</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">San Francisco</a></li>
                    </ul>
                </div>
                
                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Support</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Help Center</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Contact Us</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Booking Support</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Cancellation Policy</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Travel Insurance</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors duration-200">Safety Guidelines</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Social Media & Payment Methods -->
            <div class="border-t border-gray-800 mt-12 pt-8">
                <div class="flex flex-col lg:flex-row justify-between items-center space-y-6 lg:space-y-0">
                    <!-- Social Media -->
                    <div class="flex items-center space-x-6">
                        <span class="text-gray-300 font-medium">Follow Us:</span>
                        <div class="flex space-x-4">
                            <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-violet-500 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-violet-500 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.01-.06a12.05 12.05 0 0 0 6.53 1.92c7.85 0 12.16-6.5 12.16-12.14 0-.18 0-.36-.01-.54A8.69 8.69 0 0 0 24 6z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-violet-500 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-violet-500 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.749.097.118.112.219.083.402-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-violet-500 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.225 12.225c-1.154-1.154-2.896-2.017-5.086-2.017s-3.932.863-5.086 2.017c-1.154 1.154-2.025 2.904-2.025 5.102 0 2.198.871 3.948 2.025 5.102 1.154 1.154 2.896 2.017 5.086 2.017s3.932-.863 5.086-2.017c1.154-1.154 2.025-2.904 2.025-5.102 0-2.198-.871-3.948-2.025-5.102zM12 7.475c2.17 0 2.444.01 3.298.048.874.04 1.358.187 1.677.31.421.164.72.359 1.035.675.315.315.511.613.675 1.035.123.319.27.803.31 1.677.039.854.048 1.129.048 3.298s-.01 2.444-.048 3.298c-.04.874-.187 1.358-.31 1.677-.164.421-.359.72-.675 1.035-.315.315-.613.511-1.035.675-.319.123-.803.27-1.677.31-.854.039-1.129.048-3.298.048s-2.444-.01-3.298-.048c-.874-.04-1.358-.187-1.677-.31-.421-.164-.72-.359-1.035-.675-.315-.315-.511-.613-.675-1.035-.123-.319-.27-.803-.31-1.677-.039-.854-.048-1.129-.048-3.298s.01-2.444.048-3.298c.04-.874.187-1.358.31-1.677.164-.421.359-.72.675-1.035.315-.315.613-.511 1.035-.675.319-.123.803-.27 1.677-.31.854-.039 1.129-.048 3.298-.048L12 7.475z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="flex items-center space-x-6">
                        <span class="text-gray-300 font-medium">We Accept:</span>
                        <div class="flex space-x-4">
                            <div class="w-12 h-8 bg-white rounded flex items-center justify-center">
                                <svg class="w-8 h-5" viewBox="0 0 32 20" fill="none">
                                    <rect width="32" height="20" rx="4" fill="white"/>
                                    <path d="M11.5 8.5H9V11.5H11.5V8.5Z" fill="#FF5F00"/>
                                    <path d="M12.5 8.5H15V11.5H12.5V8.5Z" fill="#EB001B"/>
                                    <path d="M8 8.5H10.5V11.5H8V8.5Z" fill="#F79E1B"/>
                                </svg>
                            </div>
                            <div class="w-12 h-8 bg-white rounded flex items-center justify-center">
                                <svg class="w-8 h-5" viewBox="0 0 32 20" fill="none">
                                    <rect width="32" height="20" rx="4" fill="white"/>
                                    <path d="M6 8H10L12 12L14 8H18L15 14H9L6 8Z" fill="#1434CB"/>
                                </svg>
                            </div>
                            <div class="w-12 h-8 bg-white rounded flex items-center justify-center">
                                <svg class="w-8 h-5" viewBox="0 0 32 20" fill="none">
                                    <rect width="32" height="20" rx="4" fill="white"/>
                                    <path d="M8 8H24V12H8V8Z" fill="#00A1E4"/>
                                </svg>
                            </div>
                            <div class="w-12 h-8 bg-white rounded flex items-center justify-center">
                                <svg class="w-8 h-5" viewBox="0 0 32 20" fill="none">
                                    <rect width="32" height="20" rx="4" fill="white"/>
                                    <path d="M10 8H22C23 8 24 9 24 10C24 11 23 12 22 12H10C9 12 8 11 8 10C8 9 9 8 10 8Z" fill="#FF9900"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Copyright -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="text-gray-400 text-sm">
                        © {{ date('Y') }} TravelBook. All rights reserved.
                    </div>
                    <div class="flex items-center space-x-6 text-sm">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Privacy Policy</a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Terms of Service</a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Cookie Policy</a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @filamentScripts
</body>
</html>
