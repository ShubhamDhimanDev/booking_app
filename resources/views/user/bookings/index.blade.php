@extends('layouts.app')

@section('title', 'My Bookings - MeetFlow')

@section('content')
<div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1
                    class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="material-icons-round text-primary text-4xl">dashboard_customize</span>
                    My Bookings
                </h1>
                <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-2xl">
                    Track, manage, and reschedule your upcoming professional consultations and events in one beautiful
                    workspace.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-8">
            <button onclick="filterBookings('all')" data-filter="all" class="filter-btn px-4 py-1.5 rounded-full text-xs font-bold bg-primary text-white">All Events
                ({{ $totalCount }})</button>
            @if ($pendingCount > 0)
                <button onclick="filterBookings('pending')" data-filter="pending"
                    class="filter-btn px-4 py-1.5 rounded-full text-xs font-bold bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors">Pending
                    ({{ $pendingCount }})</button>
            @endif
            @if ($confirmedCount > 0)
                <button onclick="filterBookings('confirmed')" data-filter="confirmed"
                    class="filter-btn px-4 py-1.5 rounded-full text-xs font-bold bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors">Confirmed
                    ({{ $confirmedCount }})</button>
            @endif
            @if ($cancelledCount > 0)
                <button onclick="filterBookings('cancelled')" data-filter="cancelled"
                    class="filter-btn px-4 py-1.5 rounded-full text-xs font-bold bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors">Cancelled
                    ({{ $cancelledCount }})</button>
            @endif
        </div>

        <!-- Loading Spinner -->
        <div id="bookings-loader" class="hidden">
            <div class="flex items-center justify-center py-20">
                <div class="relative">
                    <div class="w-16 h-16 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="material-icons-round text-primary text-2xl animate-pulse">event</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="bookings-container" class="bookings-fade-in">
            @include('user.bookings.partials.bookings-grid', ['bookings' => $bookings])
        </div>
@endsection

@push('scripts')
<style>
    /* Fade-in animation for container */
    #bookings-container {
        transition: opacity 0.3s ease-out, transform 0.3s ease-out;
    }

    /* Staggered fade-in animation for booking cards */
    .booking-card {
        animation: fadeInUp 0.5s ease-out forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Apply staggered delay to cards */
    .booking-card:nth-child(1) { animation-delay: 0.05s; }
    .booking-card:nth-child(2) { animation-delay: 0.1s; }
    .booking-card:nth-child(3) { animation-delay: 0.15s; }
    .booking-card:nth-child(4) { animation-delay: 0.2s; }
    .booking-card:nth-child(5) { animation-delay: 0.25s; }
    .booking-card:nth-child(6) { animation-delay: 0.3s; }
    .booking-card:nth-child(7) { animation-delay: 0.35s; }
    .booking-card:nth-child(8) { animation-delay: 0.4s; }
    .booking-card:nth-child(9) { animation-delay: 0.45s; }

    /* Pulse animation for loader icon */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.5;
            transform: scale(0.95);
        }
    }
</style>
<script>
    // Track page view for bookings list
    if (typeof fbq === 'function') {
        fbq('trackCustom', 'ViewBookings', {
            user_id: '{{ auth()->id() }}'
        });
    }

    let currentFilter = 'all';

    // AJAX Filter bookings functionality
    function filterBookings(status) {
        currentFilter = status;
        loadBookings(1, status);
    }

    // Load bookings via AJAX
    function loadBookings(page = 1, status = 'all') {
        const url = new URL('{{ route('user.bookings.index') }}');
        url.searchParams.set('page', page);
        if (status !== 'all') {
            url.searchParams.set('status', status);
        }

        const loader = document.getElementById('bookings-loader');
        const container = document.getElementById('bookings-container');

        // Show loader and fade out container
        container.style.opacity = '0';
        container.style.transform = 'translateY(10px)';
        setTimeout(() => {
            container.classList.add('hidden');
            loader.classList.remove('hidden');
        }, 200);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update the bookings grid
            container.innerHTML = data.html;

            // Hide loader and show container with animation
            loader.classList.add('hidden');
            container.classList.remove('hidden');

            // Trigger reflow to ensure animation works
            void container.offsetWidth;

            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';

            // Update filter buttons
            updateFilterButtons(status);

            // Scroll to top smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error loading bookings:', error);
            // Hide loader on error
            loader.classList.add('hidden');
            container.classList.remove('hidden');
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        });
    }

    // Update filter button states
    function updateFilterButtons(status) {
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(btn => {
            if (btn.dataset.filter === status) {
                btn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-400', 'border', 'border-slate-200', 'dark:border-slate-700');
                btn.classList.add('bg-primary', 'text-white');
            } else {
                btn.classList.remove('bg-primary', 'text-white');
                btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-400', 'border', 'border-slate-200', 'dark:border-slate-700');
            }
        });
    }

    // Handle pagination clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('a[href*="page="]')) {
            e.preventDefault();
            const url = new URL(e.target.closest('a').href);
            const page = url.searchParams.get('page');
            loadBookings(page, currentFilter);
        }
    });
</script>
@endpush
