<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeetFlow - Smart Booking & Calendar Management Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }

        .feature-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .7;
            }
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">

    <!-- Navigation -->
    <nav class="fixed w-full bg-gray-900/95 backdrop-blur-sm z-50 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <img src="{{ asset('admins/assets/images/logo.png') }}" alt="MeetFlow" class="h-20 w-auto">
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#features" class="text-gray-300 hover:text-white transition">Features</a>
                    <a href="#how-it-works" class="text-gray-300 hover:text-white transition">How It Works</a>
                    <a href="#pricing" class="text-gray-300 hover:text-white transition">Pricing</a>
                    <a href="#contact" class="text-gray-300 hover:text-white transition">Contact</a>
                </div>
                <div class="flex space-x-4">
                    @auth
                        <a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('user.bookings.index') }}"
                           class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 to-blue-900/20"></div>
        <div class="max-w-7xl mx-auto relative z-10">
            <div class="text-center">
                <h1 class="text-5xl md:text-7xl font-extrabold mb-6 leading-tight">
                    Smart Booking Platform
                    <span class="block gradient-text">For Modern Businesses</span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-400 mb-8 max-w-3xl mx-auto">
                    Streamline your scheduling, accept payments seamlessly, and automate reminders.
                    All synced with your Google Calendar.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="px-8 py-4 text-lg font-semibold text-white gradient-bg rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                        Start Free Trial
                    </a>
                    <a href="#how-it-works" class="px-8 py-4 text-lg font-semibold text-white bg-gray-800 rounded-lg hover:bg-gray-700 transition">
                        Watch Demo
                    </a>
                </div>
                <div class="mt-12 flex justify-center items-center space-x-8 text-sm text-gray-500">
                    <span class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> No credit card required</span>
                    <span class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> 14-day free trial</span>
                    <span class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i> Cancel anytime</span>
                </div>
            </div>
        </div>

        <!-- Floating illustration placeholder -->
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl opacity-10 floating">
            <div class="w-full h-96 bg-gradient-to-br from-purple-600 to-blue-600 rounded-full blur-3xl"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-800/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Everything You Need to <span class="gradient-text">Manage Bookings</span>
                </h2>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                    Powerful features designed to save time and grow your business
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gray-900 rounded-xl p-8 card-hover border border-gray-800">
                    <div class="w-14 h-14 gradient-bg rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-calendar-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Google Calendar Sync</h3>
                    <p class="text-gray-400">
                        Automatically sync all bookings with your Google Calendar. Generate Meet links and manage appointments in one place.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gray-900 rounded-xl p-8 card-hover border border-gray-800">
                    <div class="w-14 h-14 gradient-bg rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-credit-card text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Payment Integration</h3>
                    <p class="text-gray-400">
                        Accept payments seamlessly with Razorpay and PayU. Secure transactions with automated payment tracking.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gray-900 rounded-xl p-8 card-hover border border-gray-800">
                    <div class="w-14 h-14 gradient-bg rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-bell text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Smart Reminders</h3>
                    <p class="text-gray-400">
                        Automated email reminders for both organizers and attendees. Reduce no-shows and stay organized.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gray-900 rounded-xl p-8 card-hover border border-gray-800">
                    <div class="w-14 h-14 gradient-bg rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-clock text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Flexible Time Slots</h3>
                    <p class="text-gray-400">
                        Create custom time slots, set availability windows, and manage multiple event types with ease.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gray-900 rounded-xl p-8 card-hover border border-gray-800">
                    <div class="w-14 h-14 gradient-bg rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Team Management</h3>
                    <p class="text-gray-400">
                        Multi-user support with role-based access. Perfect for teams managing bookings together.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gray-900 rounded-xl p-8 card-hover border border-gray-800">
                    <div class="w-14 h-14 gradient-bg rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Analytics Dashboard</h3>
                    <p class="text-gray-400">
                        Track bookings, revenue, and performance metrics. Make data-driven decisions for your business.
                    </p>
                </div>

                <!-- Feature 7 -->
                <div class="bg-gray-900 rounded-xl p-8 card-hover border border-gray-800">
                    <div class="w-14 h-14 gradient-bg rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-repeat text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Easy Rescheduling</h3>
                    <p class="text-gray-400">
                        Allow clients to reschedule appointments with a single click. Automatic notifications for all changes.
                    </p>
                </div>

                <!-- Feature 8 -->
                <div class="bg-gray-900 rounded-xl p-8 card-hover border border-gray-800">
                    <div class="w-14 h-14 gradient-bg rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Secure & Reliable</h3>
                    <p class="text-gray-400">
                        Enterprise-grade security with encrypted data storage. Your information is always protected.
                    </p>
                </div>

                <!-- Feature 9 -->
                <div class="bg-gray-900 rounded-xl p-8 card-hover border border-gray-800">
                    <div class="w-14 h-14 gradient-bg rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-mobile-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Mobile Responsive</h3>
                    <p class="text-gray-400">
                        Perfect experience on any device. Manage bookings on the go from your phone or tablet.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Get Started in <span class="gradient-text">3 Simple Steps</span>
                </h2>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                    From setup to your first booking in minutes
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Step 1 -->
                <div class="text-center relative">
                    <div class="w-20 h-20 gradient-bg rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold text-white pulse-animation">
                        1
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Create Your Account</h3>
                    <p class="text-gray-400">
                        Sign up in seconds and connect your Google Calendar. Set your availability and preferences.
                    </p>
                    <div class="hidden md:block absolute top-10 -right-12 text-purple-600 text-4xl">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="text-center relative">
                    <div class="w-20 h-20 gradient-bg rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold text-white pulse-animation" style="animation-delay: 0.3s;">
                        2
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Configure Your Events</h3>
                    <p class="text-gray-400">
                        Create event types, set pricing, and customize your booking page. Add your payment details.
                    </p>
                    <div class="hidden md:block absolute top-10 -right-12 text-purple-600 text-4xl">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-20 h-20 gradient-bg rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold text-white pulse-animation" style="animation-delay: 0.6s;">
                        3
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Share & Accept Bookings</h3>
                    <p class="text-gray-400">
                        Share your booking link and start accepting appointments. Get paid automatically.
                    </p>
                </div>
            </div>

            <div class="mt-16 text-center">
                <a href="{{ route('register') }}" class="inline-block px-8 py-4 text-lg font-semibold text-white gradient-bg rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                    Start Your Free Trial Now
                </a>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-800/50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Simple, <span class="gradient-text">Transparent Pricing</span>
                </h2>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                    Choose the plan that fits your needs. Upgrade or downgrade anytime.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Starter Plan -->
                <div class="bg-gray-900 rounded-xl p-8 border border-gray-800 hover:border-purple-600 transition">
                    <h3 class="text-2xl font-bold mb-2">Starter</h3>
                    <p class="text-gray-400 mb-6">Perfect for individuals</p>
                    <div class="mb-6">
                        <span class="text-5xl font-bold">$9</span>
                        <span class="text-gray-400">/month</span>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Up to 50 bookings/month</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Google Calendar sync</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Email reminders</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Payment integration</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Basic support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full py-3 text-center font-semibold text-white bg-gray-800 rounded-lg hover:bg-gray-700 transition">
                        Get Started
                    </a>
                </div>

                <!-- Professional Plan -->
                <div class="bg-gray-900 rounded-xl p-8 border-2 border-purple-600 relative transform scale-105 shadow-2xl">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 px-4 py-1 gradient-bg rounded-full text-sm font-semibold">
                        Most Popular
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Professional</h3>
                    <p class="text-gray-400 mb-6">For growing businesses</p>
                    <div class="mb-6">
                        <span class="text-5xl font-bold">$29</span>
                        <span class="text-gray-400">/month</span>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Unlimited bookings</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Google Calendar sync</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> SMS & Email reminders</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Payment integration</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Team management</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Analytics dashboard</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Priority support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full py-3 text-center font-semibold text-white gradient-bg rounded-lg hover:shadow-lg transition">
                        Get Started
                    </a>
                </div>

                <!-- Enterprise Plan -->
                <div class="bg-gray-900 rounded-xl p-8 border border-gray-800 hover:border-purple-600 transition">
                    <h3 class="text-2xl font-bold mb-2">Enterprise</h3>
                    <p class="text-gray-400 mb-6">For large organizations</p>
                    <div class="mb-6">
                        <span class="text-5xl font-bold">Custom</span>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Everything in Professional</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Custom integrations</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> White-label options</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> Dedicated account manager</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> 24/7 support</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i> SLA guarantee</li>
                    </ul>
                    <a href="#contact" class="block w-full py-3 text-center font-semibold text-white bg-gray-800 rounded-lg hover:bg-gray-700 transition">
                        Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Ready to Transform Your <span class="gradient-text">Booking Experience?</span>
            </h2>
            <p class="text-xl text-gray-400 mb-8">
                Join thousands of professionals who trust MeetFlow for their scheduling needs
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="px-8 py-4 text-lg font-semibold text-white gradient-bg rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition">
                    Start Free Trial
                </a>
                <a href="#contact" class="px-8 py-4 text-lg font-semibold text-white bg-gray-800 rounded-lg hover:bg-gray-700 transition">
                    Schedule a Demo
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-950 border-t border-gray-800 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-1">
                    <div class="flex items-center mb-4">
                        <img src="{{ asset('admins/assets/images/logo.png') }}" alt="MeetFlow" class="h-20">
                    </div>
                    <p class="text-gray-400 text-sm">
                        Smart booking and calendar management platform for modern businesses.
                    </p>
                </div>

                <!-- Product -->
                <div>
                    <h4 class="font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Features</a></li>
                        <li><a href="#pricing" class="text-gray-400 hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Integrations</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">API</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Cookie Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">GDPR</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">© {{ date('Y') }} MeetFlow. All rights reserved.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
