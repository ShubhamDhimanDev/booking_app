import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Alpine.js stores for theme and sidebar management
document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        init() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            this.theme = savedTheme || systemTheme;
            this.updateTheme();
        },
        theme: 'light',
        toggle() {
            this.theme = this.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', this.theme);
            this.updateTheme();
        },
        updateTheme() {
            const html = document.documentElement;
            const body = document.body;
            if (this.theme === 'dark') {
                html.classList.add('dark');
                body.classList.add('dark', 'bg-gray-900');
            } else {
                html.classList.remove('dark');
                body.classList.remove('dark', 'bg-gray-900');
            }
        }
    });

    Alpine.store('sidebar', {
        // Initialize based on screen size
        isExpanded: window.innerWidth >= 1024, // true for desktop, false for mobile
        isMobileOpen: false,
        isHovered: false,

        toggleExpanded() {
            this.isExpanded = !this.isExpanded;
            // When toggling desktop sidebar, ensure mobile menu is closed
            this.isMobileOpen = false;
        },

        toggleMobileOpen() {
            this.isMobileOpen = !this.isMobileOpen;
            // Don't modify isExpanded when toggling mobile menu
        },

        setMobileOpen(val) {
            this.isMobileOpen = val;
        },

        setHovered(val) {
            // Only allow hover effects on desktop when sidebar is collapsed
            if (window.innerWidth >= 1024 && !this.isExpanded) {
                this.isHovered = val;
            }
        }
    });
});

Alpine.start();
