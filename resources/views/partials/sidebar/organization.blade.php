<aside
    :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
>
    <!-- SIDEBAR HEADER -->
    <div
        :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="flex items-center gap-2 pt-8 sidebar-header pb-7"
    >
        <a href="{{ route('organization.dashboard') }}">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <span class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ config('app.name', 'MeetFlow') }}</span>
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
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">MENU</span>
                </h3>

                <ul class="flex flex-col gap-4 mb-6">
                    <!-- Dashboard -->
                    <li>
                        <a
                            href="{{ route('organization.dashboard') }}"
                            class="menu-item group {{ request()->routeIs('organization.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('organization.dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill=""/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Dashboard</span>
                        </a>
                    </li>

                    <!-- Events -->
                    <li>
                        <a
                            href="{{ route('organization.events.index') }}"
                            class="menu-item group {{ request()->routeIs('organization.events.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('organization.events.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z" fill=""/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Events</span>
                        </a>
                    </li>

                    <!-- Bookings -->
                    <li>
                        <a
                            href="{{ route('organization.bookings.index') }}"
                            class="menu-item group {{ request()->routeIs('organization.bookings.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('organization.bookings.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.73242 3.25C4.48042 3.25 2.73242 5.052 2.73242 7.25V16.75C2.73242 18.948 4.48042 20.75 6.73242 20.75H17.2676C19.5196 20.75 21.2676 18.948 21.2676 16.75V7.25C21.2676 5.052 19.5196 3.25 17.2676 3.25H6.73242ZM4.23242 7.25C4.23242 5.858 5.31242 4.75 6.73242 4.75H17.2676C18.6876 4.75 19.7676 5.858 19.7676 7.25V8.5H4.23242V7.25ZM4.23242 10V16.75C4.23242 18.142 5.31242 19.25 6.73242 19.25H17.2676C18.6876 19.25 19.7676 18.142 19.7676 16.75V10H4.23242Z" fill=""/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Bookings</span>
                        </a>
                    </li>

                    <!-- Payments -->
                    <li>
                        <a
                            href="{{ route('organization.payments.index') }}"
                            class="menu-item group {{ request()->routeIs('organization.payments.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('organization.payments.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2C17.52 2 22 6.48 22 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.99998 12L10.58 14.58L16 9.17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Payments</span>
                        </a>
                    </li>

                    <!-- Team -->
                    @can('view users')
                    <li>
                        <a
                            href="{{ route('organization.team.index') }}"
                            class="menu-item group {{ request()->routeIs('organization.team.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('organization.team.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 7.16C17.94 7.15 17.87 7.15 17.81 7.16C16.43 7.11 15.33 5.98 15.33 4.58C15.33 3.15 16.48 2 17.91 2C19.34 2 20.49 3.16 20.49 4.58C20.48 5.98 19.38 7.11 18 7.16Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16.97 14.44C18.34 14.67 19.85 14.43 20.91 13.72C22.32 12.78 22.32 11.24 20.91 10.3C19.84 9.59 18.31 9.35 16.94 9.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M5.97 7.16C6.03 7.15 6.1 7.15 6.16 7.16C7.54 7.11 8.64 5.98 8.64 4.58C8.64 3.15 7.49 2 6.06 2C4.63 2 3.48 3.16 3.48 4.58C3.49 5.98 4.59 7.11 5.97 7.16Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7 14.44C5.63 14.67 4.12 14.43 3.06 13.72C1.65 12.78 1.65 11.24 3.06 10.3C4.13 9.59 5.66 9.35 7.03 9.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 14.63C11.94 14.62 11.87 14.62 11.81 14.63C10.43 14.58 9.33 13.45 9.33 12.05C9.33 10.62 10.48 9.47 11.91 9.47C13.34 9.47 14.49 10.63 14.49 12.05C14.48 13.45 13.38 14.59 12 14.63Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.09 17.78C7.68 18.72 7.68 20.26 9.09 21.2C10.69 22.27 13.31 22.27 14.91 21.2C16.32 20.26 16.32 18.72 14.91 17.78C13.32 16.72 10.69 16.72 9.09 17.78Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Team</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>

            <!-- Menu Group - Billing -->
            <div>
                <h3 class="mb-4 mt-4 text-xs uppercase leading-[20px] text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">BILLING</span>
                </h3>

                <ul class="flex flex-col gap-4 mb-6">
                    <!-- Subscription -->
                    <li>
                        <a
                            href="{{ route('organization.subscription') }}"
                            class="menu-item group {{ request()->routeIs('organization.subscription*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('organization.subscription*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 8.50488H22" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 16.5049H8" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10.5 16.5049H14.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6.44 3.50488H17.55C21.11 3.50488 22 4.38488 22 7.89488V16.1049C22 19.6149 21.11 20.4949 17.56 20.4949H6.44C2.89 20.5049 2 19.6249 2 16.1149V7.89488C2 4.38488 2.89 3.50488 6.44 3.50488Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Subscription</span>
                        </a>
                    </li>

                    <!-- Invoices -->
                    <li>
                        <a
                            href="{{ route('organization.invoices.index') }}"
                            class="menu-item group {{ request()->routeIs('organization.invoices.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('organization.invoices.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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

            <!-- Menu Group - Settings -->
            <div>
                <h3 class="mb-4 mt-4 text-xs uppercase leading-[20px] text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">SETTINGS</span>
                </h3>

                <ul class="flex flex-col gap-4 mb-6">
                    <!-- Organization Settings -->
                    @can('manage organization settings')
                    <li>
                        <a
                            href="{{ route('organization.settings') }}"
                            class="menu-item group {{ request()->routeIs('organization.settings*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        >
                            <svg class="{{ request()->routeIs('organization.settings*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 12.88V11.12C2 10.08 2.85 9.22 3.9 9.22C5.71 9.22 6.45 7.94 5.54 6.37C5.02 5.47 5.33 4.3 6.24 3.78L7.97 2.79C8.76 2.32 9.78 2.6 10.25 3.39L10.36 3.58C11.26 5.15 12.74 5.15 13.65 3.58L13.76 3.39C14.23 2.6 15.25 2.32 16.04 2.79L17.77 3.78C18.68 4.3 18.99 5.47 18.47 6.37C17.56 7.94 18.3 9.22 20.11 9.22C21.15 9.22 22.01 10.07 22.01 11.12V12.88C22.01 13.92 21.16 14.78 20.11 14.78C18.3 14.78 17.56 16.06 18.47 17.63C18.99 18.54 18.68 19.7 17.77 20.22L16.04 21.21C15.25 21.68 14.23 21.4 13.76 20.61L13.65 20.42C12.75 18.85 11.27 18.85 10.36 20.42L10.25 20.61C9.78 21.4 8.76 21.68 7.97 21.21L6.24 20.22C5.33 19.7 5.02 18.53 5.54 17.63C6.45 16.06 5.71 14.78 3.9 14.78C2.85 14.78 2 13.92 2 12.88Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Settings</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
        </nav>
    </div>
</aside>
