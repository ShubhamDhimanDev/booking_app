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

        <!-- Cancel Booking Modal -->
        <div id="cancelModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-8 shadow-2xl transform transition-all">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <span class="material-icons-round text-red-600 dark:text-red-400 text-3xl">cancel</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">Cancel Booking</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Confirm cancellation</p>
                    </div>
                </div>

                <form id="cancelForm" method="POST" action="">
                    @csrf
                    <div class="mb-6">
                        <p class="text-slate-700 dark:text-slate-300 mb-4">
                            Are you sure you want to cancel your booking for <strong id="cancelEventTitle"></strong>?
                        </p>
                        <div id="refundInfo" class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-4 mb-4 hidden">
                            <p class="text-sm text-emerald-800 dark:text-emerald-300 font-semibold">
                                <span class="material-icons-round text-base align-middle">info</span>
                                Refund Policy
                            </p>
                            <p id="refundPolicyText" class="text-xs text-emerald-700 dark:text-emerald-400 mt-2"></p>
                        </div>
                        <div class="mb-4">
                            <label for="cancellationReason" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                Reason for cancellation <span class="text-red-500">*</span>
                            </label>
                            <textarea id="cancellationReason" name="reason" rows="4" required
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                                placeholder="Please provide a reason for cancelling this booking..."></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeCancelModal()"
                            class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-2xl font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">
                            Keep Booking
                        </button>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-bold transition-all shadow-lg shadow-red-500/20">
                            Confirm Cancel
                        </button>
                    </div>
                </form>
            </div>
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
{!! \App\Services\TrackingService::getEventScript('ViewBookings', [
    'user_id' => auth()->id(),
    'total_bookings' => $totalCount ?? 0
]) !!}
{!! \App\Services\TrackingService::getGoogleEventScript('view_bookings', [
    'user_id' => auth()->id(),
    'total_bookings' => $totalCount ?? 0
]) !!}
<script>
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

    // Cancel modal functions
    const bookingsData = @json($bookings->map(function($b) {
        $refundPolicy = null;
        if ($b->event) {
            try {
                $refundPolicy = $b->event->getRefundPolicyDescription();
            } catch (\Exception $e) {
                $refundPolicy = null;
            }
        }
        
        return [
            'id' => $b->id,
            'title' => optional($b->event)->title ?? 'Event',
            'refund_policy' => $refundPolicy
        ];
    }));

    function openCancelModal(bookingId, eventTitle) {
        const booking = bookingsData.find(b => b.id === bookingId);
        const modal = document.getElementById('cancelModal');
        const form = document.getElementById('cancelForm');
        const titleEl = document.getElementById('cancelEventTitle');
        const refundInfo = document.getElementById('refundInfo');
        const refundPolicyText = document.getElementById('refundPolicyText');

        form.action = `/user/bookings/${bookingId}/cancel`;
        titleEl.textContent = eventTitle;

        if (booking && booking.refund_policy) {
            refundPolicyText.textContent = booking.refund_policy;
            refundInfo.classList.remove('hidden');
        } else {
            refundInfo.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCancelModal() {
        const modal = document.getElementById('cancelModal');
        const form = document.getElementById('cancelForm');

        modal.classList.add('hidden');
        document.body.style.overflow = '';
        form.reset();
    }

    // Close modal on outside click
    document.getElementById('cancelModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCancelModal();
        }
    });
</script>
@endpush
