<aside
    class="fixed flex flex-col mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-9999 border-r border-gray-200"
    :class="{
        'w-[290px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
        'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full lg:translate-x-0': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)"
>
    <!-- SIDEBAR HEADER -->
    <div
        class="pt-8 pb-7 flex"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'lg:justify-center' : 'justify-start'"
    >
        <a href="{{ route('super-admin.dashboard') }}">
            <span
                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                class="flex flex-col"
            >
                <span class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ config('app.name', 'MeetFlow') }}</span>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">SUPER ADMIN</span>
            </span>
            <span
                x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen"
                class="text-xl font-bold text-brand-600 dark:text-brand-400"
            >
                M
            </span>
        </a>
    </div>
    <!-- SIDEBAR HEADER -->

    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <!-- Sidebar Menu -->
        <nav>
            <!-- Menu Group - Platform -->
            <div>
                <h3
                    class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                    :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'lg:justify-center' : 'justify-start'"
                >
                    <template x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                        <span>PLATFORM</span>
                    </template>
                    <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                        </svg>
                    </template>
                </h3>

                <ul class="flex flex-col gap-1 mb-6">
                    <!-- Dashboard -->
                    <li>
                        <a
                            href="{{ route('super-admin.dashboard') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <span class="material-icons-outlined">dashboard</span>
                            <span
                                class="menu-item-text"
                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                            >
                                Dashboard
                            </span>
                        </a>
                    </li>

                    <!-- Organizations -->
                    <li>
                        <a
                            href="{{ route('super-admin.organizations.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.organizations.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <span class="material-icons-outlined">business</span>
                            <span
                                class="menu-item-text"
                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                            >
                                Organizations
                            </span>
                        </a>
                    </li>

                    <!-- Users -->
                    <li>
                        <a
                            href="{{ route('super-admin.users.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.users.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <span class="material-icons-outlined">people</span>
                            <span
                                class="menu-item-text"
                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                            >
                                Users
                            </span>
                        </a>
                    </li>

                    <!-- Events -->
                    <li>
                        <a
                            href="{{ route('super-admin.events.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.events.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <span class="material-icons-outlined">event</span>
                            <span
                                class="menu-item-text"
                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                            >
                                Events
                            </span>
                        </a>
                    </li>

                    <!-- Bookings -->
                    <li>
                        <a
                            href="{{ route('super-admin.bookings.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.bookings.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <span class="material-icons-outlined">book_online</span>
                            <span
                                class="menu-item-text"
                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                            >
                                Bookings
                            </span>
                        </a>
                    </li>

                    <!-- Payments -->
                    <li>
                        <a
                            href="{{ route('super-admin.payments.index') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.payments.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <span class="material-icons-outlined">payments</span>
                            <span
                                class="menu-item-text"
                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                            >
                                Payments
                            </span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Menu Group - System -->
            <div>
                <h3
                    class="mb-4 mt-4 text-xs uppercase flex leading-[20px] text-gray-400"
                    :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'lg:justify-center' : 'justify-start'"
                >
                    <template x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                        <span>SYSTEM</span>
                    </template>
                    <template x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.9656 4 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                        </svg>
                    </template>
                </h3>

                <ul class="flex flex-col gap-1 mb-6">
                    <!-- Settings -->
                    <li>
                        <a
                            href="{{ route('super-admin.settings') }}"
                            class="menu-item group {{ request()->routeIs('super-admin.settings*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <span class="material-icons-outlined">settings</span>
                            <span
                                class="menu-item-text"
                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                            >
                                Settings
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</aside>
