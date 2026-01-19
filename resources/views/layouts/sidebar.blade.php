<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        @hasanyrole('owner|admin|team-member')
          {{-- Dashboard --}}
          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('admin.dashboard') }}" href="{{ route('admin.dashboard') }}">
                  <i class="mdi mdi-view-dashboard menu-icon"></i>
                  <span class="menu-title">Dashboard</span>
              </a>
          </li>

          {{-- Users --}}
          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('admin.users.*') }}" href="{{ route('admin.users.index') }}">
                  <i class="mdi mdi-account-multiple menu-icon"></i>
                  <span class="menu-title">Users</span>
              </a>
          </li>

          {{-- Events --}}
          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('admin.events.*') }}" href="{{ route('admin.events.index') }}">
                  <i class="mdi mdi-calendar menu-icon"></i>
                  <span class="menu-title">Event</span>
              </a>
          </li>

          {{-- Bookings --}}
          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('admin.bookings.*') }}" href="{{ route('admin.bookings.index') }}">
                  <i class="mdi mdi-calendar-clock menu-icon"></i>
                  <span class="menu-title">Bookings</span>
              </a>
          </li>

          {{-- Bookings --}}
          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('admin.payments.*') }}" href="{{ route('admin.payments.history') }}">
                  <i class="mdi mdi-credit-card menu-icon"></i>
                  <span class="menu-title">Payments</span>
              </a>
          </li>


          {{-- Payment Gateways --}}
          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('admin.payment-gateway.*') }}" href="{{ route('admin.payment-gateway.edit') }}">
                  <i class="mdi mdi-credit-card-settings-outline menu-icon"></i>
                  <span class="menu-title">Payment Gateways</span>
              </a>
          </li>

          {{-- Tracking Settings --}}
          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('admin.tracking.*') }}" href="{{ route('admin.tracking.index') }}">
                  <i class="mdi mdi-chart-line menu-icon"></i>
                  <span class="menu-title">Tracking Settings</span>
              </a>
          </li>

          {{-- Promo Codes --}}
          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('admin.promo-codes.*') }}" href="{{ route('admin.promo-codes.index') }}">
                  <i class="mdi mdi-tag-multiple menu-icon"></i>
                  <span class="menu-title">Promo Codes</span>
              </a>
          </li>
        @endhasanyrole

        @role('user')
          {{-- User --}}

          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('user.bookings.*') }}" href="{{ route('user.bookings.index') }}">
                  <i class="mdi mdi-calendar-clock menu-icon"></i>
                  <span class="menu-title">Bookings</span>
              </a>
          </li>

          <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('transactions.*') }}" href="{{ route('transactions.index') }}">
                  <i class="mdi mdi-credit-card menu-icon"></i>
                  <span class="menu-title">Transactions</span>
              </a>
          </li>

        @endrole
    </ul>
</nav>
