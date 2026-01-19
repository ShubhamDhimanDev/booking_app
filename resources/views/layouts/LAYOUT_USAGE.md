# Booking Layout Usage Guide

This document explains how to use the common `booking.blade.php` layout for all booking-related pages.

## Layout Features

The `booking.blade.php` layout provides:
- ✅ Dark/Light theme support
- ✅ Modern header with logo and badge
- ✅ Consistent footer
- ✅ Tailwind CSS v3 configuration
- ✅ Plus Jakarta Sans font
- ✅ Material Icons Round
- ✅ Optional loader component
- ✅ Responsive design

## Basic Usage

```blade
@extends('layouts.booking')

@section('title', 'Your Page Title')

@section('content')
    <!-- Your page content here -->
@endsection
```

## Available Sections & Yields

### Required Sections

#### `title`
Page title that appears in browser tab
```blade
@section('title', 'Booking Confirmation')
```

#### `content`
Main content area
```blade
@section('content')
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6">
        <!-- Your content -->
    </div>
@endsection
```

### Optional Customizations

#### Header Icon
```blade
@section('header-icon', 'event_available')  // Default: 'event_note'
```

#### Badge Customization
```blade
@section('badge-color', 'bg-emerald-50 dark:bg-emerald-900/30')
@section('badge-border', 'border-emerald-200 dark:border-emerald-700')
@section('badge-icon', 'check_circle')
@section('badge-text-color', 'text-emerald-600 dark:text-emerald-400')
@section('badge-text', 'Booking Confirmed')
```

#### Loader Component
Include loader if your page needs it:
```blade
@section('loader')
    <!-- Loader will be auto-generated -->
@endsection

@section('loader-text', 'Processing your booking...')
```

#### Additional Styles
```blade
@section('additional-styles')
    .custom-class {
        /* Your custom CSS */
    }
@endsection
```

#### Scripts
Add scripts to different locations:

**Head Scripts** (before </head>):
```blade
@push('head-scripts')
    {!! \App\Services\TrackingService::getBaseScript() !!}
@endpush
```

**Body Scripts** (after <body>):
```blade
@push('body-scripts')
    {!! \App\Services\TrackingService::getEventScript('PageView') !!}
@endpush
```

**Footer Scripts** (before </body>):
```blade
@push('scripts')
    <script>
        // Your JavaScript here
    </script>
@endpush
```

## Complete Example

```blade
@extends('layouts.booking')

@section('title', 'Enter Details')

{{-- Customize header --}}
@section('header-icon', 'badge')

{{-- Customize badge --}}
@section('badge-color', 'bg-blue-50 dark:bg-blue-900/30')
@section('badge-border', 'border-blue-200 dark:border-blue-700')
@section('badge-icon', 'verified_user')
@section('badge-text-color', 'text-blue-600 dark:text-blue-400')
@section('badge-text', 'Secure Booking')

{{-- Include loader --}}
@section('loader')
@section('loader-text', 'Processing your booking...')

{{-- Add tracking scripts --}}
@push('head-scripts')
    {!! \App\Services\TrackingService::getBaseScript() !!}
@endpush

@push('body-scripts')
    {!! \App\Services\TrackingService::getEventScript('InitiateCheckout', [
        'content_name' => $event->title,
        'value' => $event->price,
    ]) !!}
@endpush

{{-- Custom styles --}}
@section('additional-styles')
    .progress-step {
        transition: all 0.3s ease;
    }
    
    .progress-step.active {
        transform: scale(1.05);
    }
@endsection

{{-- Main content --}}
@section('content')
    <!-- Progress Indicator -->
    <div class="mb-10">
        <div class="flex items-center justify-center space-x-4">
            <!-- Progress steps -->
        </div>
    </div>

    <!-- Your page content -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Content here -->
    </div>
@endsection

{{-- JavaScript --}}
@push('scripts')
    <script>
        // Your page-specific JavaScript
        console.log('Page loaded');
    </script>
@endpush
```

## Badge Presets

Common badge configurations:

### Secure Booking (Default)
```blade
@section('badge-color', 'bg-blue-50 dark:bg-blue-900/30')
@section('badge-border', 'border-blue-200 dark:border-blue-700')
@section('badge-icon', 'verified_user')
@section('badge-text-color', 'text-blue-600 dark:text-blue-400')
@section('badge-text', 'Secure Booking')
```

### Booking Confirmed
```blade
@section('badge-color', 'bg-emerald-50 dark:bg-emerald-900/30')
@section('badge-border', 'border-emerald-200 dark:border-emerald-700')
@section('badge-icon', 'check_circle')
@section('badge-text-color', 'text-emerald-600 dark:text-emerald-400')
@section('badge-text', 'Booking Confirmed')
```

### Secure Checkout
```blade
@section('badge-color', 'bg-emerald-50 dark:bg-emerald-900/30')
@section('badge-border', 'border-emerald-200 dark:border-emerald-700')
@section('badge-icon', 'lock')
@section('badge-text-color', 'text-emerald-600 dark:text-emerald-400')
@section('badge-text', 'Secure Checkout')
```

## Dark Mode Support

All pages using this layout automatically support dark mode. Use these Tailwind classes:

- Background: `bg-white dark:bg-slate-800`
- Text: `text-slate-900 dark:text-white`
- Borders: `border-slate-200 dark:border-slate-700`
- Muted text: `text-slate-600 dark:text-slate-400`

## Migration Guide

To convert an existing page to use this layout:

1. **Replace the entire head section** with `@extends('layouts.booking')`
2. **Extract page title** → `@section('title', '...')`
3. **Move tracking scripts** → `@push('head-scripts')` or `@push('body-scripts')`
4. **Remove header HTML** (it's now in layout)
5. **Remove footer HTML** (it's now in layout)
6. **Wrap remaining content** in `@section('content')`
7. **Move JavaScript** to `@push('scripts')`
8. **Move custom CSS** to `@section('additional-styles')`
9. **Customize badge/icon** if needed

## Benefits

✅ **Consistency**: All pages have identical headers and footers
✅ **Maintainability**: Update header/footer in one place
✅ **Reduced Code**: Pages are now 50-70% smaller
✅ **Dark Mode**: Automatic support across all pages
✅ **Easy Updates**: Add new features to layout once, affects all pages
✅ **Type Safety**: Centralized configuration
✅ **Performance**: Shared resources load once

## Notes

- The layout includes `style.css` automatically
- Tailwind CSS is configured with primary color `#6366f1`
- Dark mode uses `class` strategy (not `media`)
- All Material Icons are available: https://fonts.google.com/icons
- Footer links can be customized by editing `booking.blade.php`
