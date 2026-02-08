<aside
    :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
>
    <!-- SIDEBAR HEADER -->
    <div
        :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="flex items-center gap-2 pt-8 sidebar-header pb-7"
    >
        <a href="{{ route('super-admin.dashboard') }}">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <span class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ config('app.name', 'MeetFlow') }}</span>
                <span class="ml-2 text-xs font-semibold text-gray-500 dark:text-gray-400">SUPER ADMIN</span>
            </span>
            <span class="logo-icon text-xl font-bold text-brand-600 dark:text-brand-400" :class="sidebarToggle ? 'lg:block' : 'hidden'">
                M
            </span>
        </a>
    </div>
    <!-- SIDEBAR HEADER -->

    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <!-- Sidebar Menu -->
        <nav x-data="{selected: $persist('{{ $activeMenu ?? 'Dashboard' }}')}">
            <!-- Menu Group -->
            <div>
                <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">PLATFORM</span>
                </h3>

                <ul class="flex flex-col gap-4 mb-6">
                    <!-- Dashboard -->
                    <li>
                        <a
                            href="{{ route('super-admin.dashboard') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill=""/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Dashboard</span>
                        </a>
                    </li>

                    <!-- Organizations -->
                    <li>
                        <a
                            href="{{ route('super-admin.organizations.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.organizations.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.organizations.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Organizations</span>
                        </a>
                    </li>

                    <!-- Users -->
                    <li>
                        <a
                            href="{{ route('super-admin.users.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.users.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.users.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z" fill=""/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Users</span>
                        </a>
                    </li>

                    <!-- All Events -->
                    <li>
                        <a
                            href="{{ route('super-admin.events.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.events.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.events.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z" fill=""/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">All Events</span>
                        </a>
                    </li>

                    <!-- All Bookings -->
                    <li>
                        <a
                            href="{{ route('super-admin.bookings.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.bookings.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.bookings.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.73242 3.25C4.48042 3.25 2.73242 5.052 2.73242 7.25V16.75C2.73242 18.948 4.48042 20.75 6.73242 20.75H17.2676C19.5196 20.75 21.2676 18.948 21.2676 16.75V7.25C21.2676 5.052 19.5196 3.25 17.2676 3.25H6.73242ZM4.23242 7.25C4.23242 5.858 5.31242 4.75 6.73242 4.75H17.2676C18.6876 4.75 19.7676 5.858 19.7676 7.25V8.5H4.23242V7.25ZM4.23242 10V16.75C4.23242 18.142 5.31242 19.25 6.73242 19.25H17.2676C18.6876 19.25 19.7676 18.142 19.7676 16.75V10H4.23242Z" fill=""/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">All Bookings</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Menu Group - Subscription Management -->
            <div>
                <h3 class="mb-4 mt-4 text-xs uppercase leading-[20px] text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">SUBSCRIPTION</span>
                </h3>

                <ul class="flex flex-col gap-4 mb-6">
                    <!-- Subscription Plans -->
                    <li>
                        <a
                            href="{{ route('super-admin.plans.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.plans.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.plans.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.5 13.75C9.5 14.72 10.25 15.5 11.17 15.5H13.05C13.85 15.5 14.5 14.82 14.5 13.97C14.5 13.06 14.1 12.73 13.51 12.52L10.5 11.47C9.91 11.26 9.51001 10.94 9.51001 10.02C9.51001 9.17999 10.16 8.48999 10.96 8.48999H12.84C13.76 8.48999 14.51 9.26999 14.51 10.24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 7.5V16.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M17 3V7H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 2L17 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Plans</span>
                        </a>
                    </li>

                    <!-- All Subscriptions -->
                    <li>
                        <a
                            href="{{ route('super-admin.subscriptions.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.subscriptions.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.subscriptions.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 8.50488H22" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 16.5049H8" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10.5 16.5049H14.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6.44 3.50488H17.55C21.11 3.50488 22 4.38488 22 7.89488V16.1049C22 19.6149 21.11 20.4949 17.56 20.4949H6.44C2.89 20.5049 2 19.6249 2 16.1149V7.89488C2 4.38488 2.89 3.50488 6.44 3.50488Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Subscriptions</span>
                        </a>
                    </li>

                    <!-- All Invoices -->
                    <li>
                        <a
                            href="{{ route('super-admin.invoices.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.invoices.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.invoices.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 10V15C22 20 20 22 15 22H9C4 22 2 20 2 15V9C2 4 4 2 9 2H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 10H18C15 10 14 9 14 6V2L22 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7 13H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7 17H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Invoices</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Menu Group - System -->
            <div>
                <h3 class="mb-4 mt-4 text-xs uppercase leading-[20px] text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">SYSTEM</span>
                </h3>

                <ul class="flex flex-col gap-4 mb-6">
                    <!-- Reports -->
                    <li>
                        <a
                            href="{{ route('super-admin.reports') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.reports*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.reports*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 22H15C20 22 22 20 22 15V9C22 4 20 2 15 2H9C4 2 2 4 2 9V15C2 20 4 22 9 22Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.33008 14.49L9.71008 11.4C10.0501 10.96 10.6801 10.88 11.1201 11.22L12.9501 12.66C13.3901 13 14.0201 12.92 14.3601 12.49L16.6701 9.51001" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Reports</span>
                        </a>
                    </li>

                    <!-- Settings -->
                    <li>
                        <a
                            href="{{ route('super-admin.settings') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.settings*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('super-admin.settings*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 12.88V11.12C2 10.08 2.85 9.22 3.9 9.22C5.71 9.22 6.45 7.94 5.54 6.37C5.02 5.47 5.33 4.3 6.24 3.78L7.97 2.79C8.76 2.32 9.78 2.6 10.25 3.39L10.36 3.58C11.26 5.15 12.74 5.15 13.65 3.58L13.76 3.39C14.23 2.6 15.25 2.32 16.04 2.79L17.77 3.78C18.68 4.3 18.99 5.47 18.47 6.37C17.56 7.94 18.3 9.22 20.11 9.22C21.15 9.22 22.01 10.07 22.01 11.12V12.88C22.01 13.92 21.16 14.78 20.11 14.78C18.3 14.78 17.56 16.06 18.47 17.63C18.99 18.54 18.68 19.7 17.77 20.22L16.04 21.21C15.25 21.68 14.23 21.4 13.76 20.61L13.65 20.42C12.75 18.85 11.27 18.85 10.36 20.42L10.25 20.61C9.78 21.4 8.76 21.68 7.97 21.21L6.24 20.22C5.33 19.7 5.02 18.53 5.54 17.63C6.45 16.06 5.71 14.78 3.9 14.78C2.85 14.78 2 13.92 2 12.88Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</aside>
