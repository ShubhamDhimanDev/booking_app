<header
    x-data="{menuToggle: false}"
    class="sticky top-0 z-99999 flex w-full border-gray-200 bg-white lg:border-b dark:border-gray-800 dark:bg-gray-900"
>
    <div class="flex grow flex-col items-center justify-between lg:flex-row lg:px-6">
        <div class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 sm:gap-4 lg:justify-normal lg:border-b-0 lg:px-0 lg:py-4 dark:border-gray-800">
            <!-- Hamburger Toggle BTN -->
            <button
                :class="sidebarToggle ? 'lg:bg-transparent dark:lg:bg-transparent bg-gray-100 dark:bg-gray-800' : ''"
                class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg border-gray-200 text-gray-500 lg:h-11 lg:w-11 lg:border dark:border-gray-800 dark:text-gray-400"
                @click.stop="sidebarToggle = !sidebarToggle"
            >
                <svg class="hidden fill-current lg:block" width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill=""/>
                </svg>
                <svg :class="sidebarToggle ? 'hidden' : 'block lg:hidden'" class="fill-current lg:hidden" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 6C3.25 5.58579 3.58579 5.25 4 5.25L20 5.25C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.41421 20.4142 6.75 20 6.75L4 6.75C3.58579 6.75 3.25 6.41422 3.25 6ZM3.25 18C3.25 17.5858 3.58579 17.25 4 17.25L20 17.25C20.4142 17.25 20.75 17.5858 20.75 18C20.75 18.4142 20.4142 18.75 20 18.75L4 18.75C3.58579 18.75 3.25 18.4142 3.25 18ZM4 11.25C3.58579 11.25 3.25 11.5858 3.25 12C3.25 12.4142 3.58579 12.75 4 12.75L12 12.75C12.4142 12.75 12.75 12.4142 12.75 12C12.75 11.5858 12.4142 11.25 12 11.25L4 11.25Z" fill=""/>
                </svg>
                <svg :class="sidebarToggle ? 'block lg:hidden' : 'hidden'" class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z" fill=""/>
                </svg>
            </button>
            <!-- Hamburger Toggle BTN -->

            <a href="{{ route('dashboard') }}" class="lg:hidden">
                <span class="text-xl font-bold text-brand-600 dark:text-brand-400">{{ config('app.name', 'MeetFlow') }}</span>
            </a>

            <!-- Application nav menu button -->
            <button
                class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800"
                :class="menuToggle ? 'bg-gray-100 dark:bg-gray-800' : ''"
                @click.stop="menuToggle = !menuToggle"
            >
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.4951C6.82745 10.4951 7.49902 11.1667 7.49902 11.9951V12.0051C7.49902 12.8335 6.82745 13.5051 5.99902 13.5051C5.1706 13.5051 4.49902 12.8335 4.49902 12.0051V11.9951C4.49902 11.1667 5.1706 10.4951 5.99902 10.4951ZM17.999 10.4951C18.8275 10.4951 19.499 11.1667 19.499 11.9951V12.0051C19.499 12.8335 18.8275 13.5051 17.999 13.5051C17.1706 13.5051 16.499 12.8335 16.499 12.0051V11.9951C16.499 11.1667 17.1706 10.4951 17.999 10.4951ZM13.499 11.9951C13.499 11.1667 12.8275 10.4951 11.999 10.4951C11.1706 10.4951 10.499 11.1667 10.499 11.9951V12.0051C10.499 12.8335 11.1706 13.5051 11.999 13.5051C12.8275 13.5051 13.499 12.8335 13.499 12.0051V11.9951Z" fill=""/>
                </svg>
            </button>
            <!-- Application nav menu button -->
        </div>

        <div
            :class="menuToggle ? 'flex' : 'hidden'"
            class="shadow-theme-md w-full items-center justify-between gap-4 px-5 py-4 lg:flex lg:justify-end lg:px-0 lg:shadow-none"
        >
            <div class="2xsm:gap-3 flex items-center gap-2">
                <!-- Dark Mode Toggler -->
                <button
                    class="hover:text-dark-900 relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                    @click.prevent="darkMode = !darkMode"
                >
                    <svg :class="darkMode ? 'hidden' : 'block'" class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 17C14.7614 17 17 14.7614 17 12C17 9.23858 14.7614 7 12 7C9.23858 7 7 9.23858 7 12C7 14.7614 9.23858 17 12 17ZM12 5V3M12 21V19M19 12H21M3 12H5M18.364 5.636L19.778 4.222M4.222 19.778L5.636 18.364M18.364 18.364L19.778 19.778M4.222 4.222L5.636 5.636" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg :class="darkMode ? 'block' : 'hidden'" class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21.0672 11.8568L20.4253 11.469L21.0672 11.8568ZM12.1432 2.93276L11.7553 2.29085V2.29085L12.1432 2.93276ZM7.37554 20.013C7.017 19.8056 6.5582 19.9281 6.3508 20.2866C6.1434 20.6452 6.26591 21.1039 6.62446 21.3113L7.37554 20.013ZM2.68862 17.3755C2.89602 17.7341 3.35482 17.8566 3.71337 17.6492C4.07191 17.4418 4.19442 16.983 3.98702 16.6245L2.68862 17.3755ZM21.25 12C21.25 17.1086 17.1086 21.25 12 21.25V22.75C17.9371 22.75 22.75 17.9371 22.75 12H21.25ZM2.75 12C2.75 6.89137 6.89137 2.75 12 2.75V1.25C6.06294 1.25 1.25 6.06294 1.25 12H2.75ZM15.5 14.25C12.3244 14.25 9.75 11.6756 9.75 8.5H8.25C8.25 12.5041 11.4959 15.75 15.5 15.75V14.25ZM20.4253 11.469C19.4172 13.1373 17.5882 14.25 15.5 14.25V15.75C18.1349 15.75 20.4407 14.3439 21.7092 12.2447L20.4253 11.469ZM9.75 8.5C9.75 6.41182 10.8627 4.5828 12.531 3.57467L11.7553 2.29085C9.65609 3.5593 8.25 5.86509 8.25 8.5H9.75ZM12 2.75C11.9115 2.75 11.8077 2.71008 11.7324 2.63168C11.6686 2.56527 11.6538 2.50244 11.6503 2.47703C11.6461 2.44587 11.6482 2.35557 11.7553 2.29085L12.531 3.57467C13.0342 3.27065 13.196 2.71398 13.1368 2.27627C13.0754 1.82126 12.7166 1.25 12 1.25V2.75ZM21.7092 12.2447C21.6444 12.3518 21.5541 12.3539 21.523 12.3497C21.4976 12.3462 21.4347 12.3314 21.3683 12.2676C21.2899 12.1923 21.25 12.0885 21.25 12H22.75C22.75 11.2834 22.1787 10.9246 21.7237 10.8632C21.286 10.804 20.7293 10.9658 20.4253 11.469L21.7092 12.2447ZM12 21.25C10.3139 21.25 8.73533 20.7996 7.37554 20.013L6.62446 21.3113C8.2064 22.2265 10.0432 22.75 12 22.75V21.25ZM3.98702 16.6245C3.20043 15.2647 2.75 13.6861 2.75 12H1.25C1.25 13.9568 1.77351 15.7936 2.68862 17.3755L3.98702 16.6245Z" fill="currentColor"/>
                    </svg>
                </button>
                <!-- Dark Mode Toggler -->

                <!-- User Area -->
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button
                        @click="dropdownOpen = !dropdownOpen"
                        class="flex items-center gap-3 rounded-full hover:bg-gray-50 dark:hover:bg-gray-800 p-2"
                    >
                        <span class="hidden text-right lg:block">
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                {{ auth()->user()->name }}
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                @if(auth()->user()->hasRole('super-admin'))
                                    Super Admin
                                @elseif(auth()->user()->hasRole('org-owner'))
                                    Organization Owner
                                @elseif(auth()->user()->hasRole('org-admin'))
                                    Organization Admin
                                @elseif(auth()->user()->hasRole('org-member'))
                                    Team Member
                                @else
                                    User
                                @endif
                            </span>
                        </span>

                        <span class="h-10 w-10 rounded-full bg-brand-500 flex items-center justify-center text-white font-semibold">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </span>

                        <svg class="hidden fill-current sm:block" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.410765 0.910734C0.736202 0.585297 1.26384 0.585297 1.58928 0.910734L6.00002 5.32148L10.4108 0.910734C10.7362 0.585297 11.2638 0.585297 11.5893 0.910734C11.9147 1.23617 11.9147 1.76381 11.5893 2.08924L6.58928 7.08924C6.26384 7.41468 5.7362 7.41468 5.41077 7.08924L0.410765 2.08924C0.0853277 1.76381 0.0853277 1.23617 0.410765 0.910734Z" fill=""/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        x-show="dropdownOpen"
                        @click.outside="dropdownOpen = false"
                        class="absolute right-0 mt-2 w-48 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900"
                        x-transition
                        x-cloak
                    >
                        <div class="p-2">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">
                                <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.0002 9.11245C7.7002 9.11245 6.6377 8.05 6.6377 6.75C6.6377 5.45 7.7002 4.3875 9.0002 4.3875C10.3002 4.3875 11.3627 5.45 11.3627 6.75C11.3627 8.05 10.3002 9.11245 9.0002 9.11245ZM9.0002 5.45625C8.30645 5.45625 7.7252 6.0375 7.7252 6.75C7.7252 7.4625 8.30645 8.04375 9.0002 8.04375C9.69395 8.04375 10.2752 7.4625 10.2752 6.75C10.2752 6.0375 9.69395 5.45625 9.0002 5.45625Z" fill=""/>
                                    <path d="M9 13.9875C6.525 13.9875 4.725 12.9 4.725 11.5875C4.725 10.275 6.525 9.1875 9 9.1875C11.475 9.1875 13.275 10.275 13.275 11.5875C13.275 12.9 11.475 13.9875 9 13.9875ZM9 10.275C6.8625 10.275 5.8125 11.175 5.8125 11.5875C5.8125 12 6.8625 12.9 9 12.9C11.1375 12.9 12.1875 12 12.1875 11.5875C12.1875 11.175 11.1375 10.275 9 10.275Z" fill=""/>
                                </svg>
                                <span>My Profile</span>
                            </a>
                            <a href="{{ route('settings') }}" class="flex items-center gap-3 rounded-lg px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">
                                <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.00002 0.5625C4.46252 0.5625 0.787521 4.2375 0.787521 8.775C0.787521 13.3125 4.46252 16.9875 9.00002 16.9875C13.5375 16.9875 17.2125 13.3125 17.2125 8.775C17.2125 4.2375 13.5375 0.5625 9.00002 0.5625ZM9.00002 15.9C5.0625 15.9 1.87502 12.7125 1.87502 8.775C1.87502 4.8375 5.0625 1.65 9.00002 1.65C12.9375 1.65 16.125 4.8375 16.125 8.775C16.125 12.7125 12.9375 15.9 9.00002 15.9Z" fill=""/>
                                    <path d="M9 7.0875C8.6625 7.0875 8.4375 7.3125 8.4375 7.65V12.15C8.4375 12.4875 8.6625 12.7125 9 12.7125C9.3375 12.7125 9.5625 12.4875 9.5625 12.15V7.65C9.5625 7.3125 9.3375 7.0875 9 7.0875Z" fill=""/>
                                    <circle cx="9" cy="5.175" r="0.75" fill=""/>
                                </svg>
                                <span>Settings</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">
                                    <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.025 0.9375H6.975C3.6 0.9375 1.8 2.7375 1.8 6.1125V11.8875C1.8 15.2625 3.6 17.0625 6.975 17.0625H11.025C14.4 17.0625 16.2 15.2625 16.2 11.8875V6.1125C16.2 2.7375 14.4 0.9375 11.025 0.9375ZM11.475 9.5625H7.65C7.3125 9.5625 7.0875 9.3375 7.0875 9C7.0875 8.6625 7.3125 8.4375 7.65 8.4375H11.475C11.8125 8.4375 12.0375 8.6625 12.0375 9C12.0375 9.3375 11.8125 9.5625 11.475 9.5625Z" fill=""/>
                                    </svg>
                                    <span>Log Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- User Area -->
            </div>
        </div>
    </div>
</header>
