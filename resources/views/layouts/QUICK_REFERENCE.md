# Quick Reference: Using booking.blade.php Layout

## Minimal Example
```blade
@extends('layouts.booking')
@section('title', 'Page Title')
@section('content')
    <!-- Your content -->
@endsection
```

## Common Patterns

### Secure Booking Badge (Blue)
```blade
@section('badge-color', 'bg-blue-50 dark:bg-blue-900/30')
@section('badge-border', 'border-blue-200 dark:border-blue-700')
@section('badge-icon', 'verified_user')
@section('badge-text-color', 'text-blue-600 dark:text-blue-400')
@section('badge-text', 'Secure Booking')
```

### Success Badge (Green)
```blade
@section('badge-color', 'bg-emerald-50 dark:bg-emerald-900/30')
@section('badge-border', 'border-emerald-200 dark:border-emerald-700')
@section('badge-icon', 'check_circle')
@section('badge-text-color', 'text-emerald-600 dark:text-emerald-400')
@section('badge-text', 'Booking Confirmed')
```

### Payment Badge (Emerald + Lock)
```blade
@section('badge-color', 'bg-emerald-50 dark:bg-emerald-900/30')
@section('badge-border', 'border-emerald-200 dark:border-emerald-700')
@section('badge-icon', 'lock')
@section('badge-text-color', 'text-emerald-600 dark:text-emerald-400')
@section('badge-text', 'Secure Checkout')
```

### With Loader
```blade
@section('loader')
@section('loader-text', 'Processing...')
```

### Add Scripts
```blade
@push('head-scripts')
    {!! TrackingService::getBaseScript() !!}
@endpush

@push('scripts')
    <script>
        console.log('Page loaded');
    </script>
@endpush
```

### Custom Styles
```blade
@section('additional-styles')
    .my-class {
        transition: all 0.3s ease;
    }
@endsection
```

## Dark Mode Classes

### Backgrounds
```blade
bg-white dark:bg-slate-800                      {{-- Cards --}}
bg-slate-50 dark:bg-slate-700/50                {{-- Sections --}}
bg-slate-100 dark:bg-slate-700                  {{-- Buttons --}}
```

### Text
```blade
text-slate-900 dark:text-white                  {{-- Headings --}}
text-slate-600 dark:text-slate-400              {{-- Body --}}
text-slate-500 dark:text-slate-400              {{-- Muted --}}
```

### Borders
```blade
border-slate-200 dark:border-slate-700          {{-- Standard --}}
border-slate-100 dark:border-slate-700          {{-- Subtle --}}
```

## Progress Indicator Template
```blade
<div class="mb-10">
    <div class="flex items-center justify-center space-x-4">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg">
                <span class="material-icons-round text-white text-sm">check</span>
            </div>
            <span class="ml-2 text-sm font-bold text-emerald-700 dark:text-emerald-400">Step 1</span>
        </div>
        <div class="w-32 h-1 bg-gradient-to-r from-emerald-500 to-primary rounded-full"></div>
        {{-- Repeat for more steps --}}
    </div>
</div>
```

## Card Template
```blade
<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 hover:shadow-xl transition-all">
    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
        <span class="material-icons-round text-primary text-3xl">icon_name</span>
        Title
    </h3>
    <!-- Content -->
</div>
```

## Info Box Template
```blade
<div class="bg-slate-50 dark:bg-slate-700/50 rounded-2xl p-4">
    <div class="flex items-start space-x-3">
        <span class="material-icons-round text-slate-600 dark:text-slate-400 text-xl">icon</span>
        <div class="flex-1">
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Label</p>
            <p class="text-base font-bold text-slate-900 dark:text-white">Value</p>
        </div>
    </div>
</div>
```

## Alert/Notice Template
```blade
{{-- Success --}}
<div class="bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 px-4 py-3 rounded-xl">
    <div class="flex items-start space-x-3">
        <span class="material-icons-round text-emerald-600 dark:text-emerald-400 text-xl">check_circle</span>
        <p class="text-sm text-emerald-800 dark:text-emerald-300 font-medium">Message</p>
    </div>
</div>

{{-- Warning --}}
<div class="bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 px-4 py-3 rounded-xl">
    <div class="flex items-start space-x-3">
        <span class="material-icons-round text-amber-600 dark:text-amber-400 text-xl">warning</span>
        <p class="text-sm text-amber-800 dark:text-amber-300 font-medium">Message</p>
    </div>
</div>

{{-- Error --}}
<div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 px-4 py-3 rounded-xl">
    <div class="flex items-start space-x-3">
        <span class="material-icons-round text-red-600 dark:text-red-400 text-xl">error</span>
        <p class="text-sm text-red-800 dark:text-red-300 font-medium">Message</p>
    </div>
</div>
```

## Button Templates
```blade
{{-- Primary --}}
<button class="px-6 py-4 rounded-2xl bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-extrabold transition-all shadow-lg shadow-primary/30">
    <span class="material-icons-round">icon</span>
    Button Text
</button>

{{-- Success --}}
<button class="px-6 py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-green-600 hover:opacity-95 text-white font-extrabold transition-all shadow-lg shadow-emerald-500/30">
    <span class="material-icons-round">check</span>
    Success
</button>

{{-- Secondary --}}
<button class="px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold transition-all shadow-sm">
    <span class="material-icons-round">arrow_back</span>
    Back
</button>
```

## Two Column Layout
```blade
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
    {{-- Left Column --}}
    <div>
        <!-- Content -->
    </div>
    
    {{-- Right Column --}}
    <div>
        <!-- Content -->
    </div>
</div>
```

## Gradient Card (Highlighted)
```blade
<div class="bg-gradient-to-br from-primary/5 via-indigo-50 to-purple-50 dark:from-primary/20 dark:via-slate-700 dark:to-purple-900/20 rounded-2xl p-4 border-2 border-primary/20 dark:border-primary/30">
    <!-- Content -->
</div>
```

## Material Icons (Common)
- `event_note` - Calendar/Event
- `schedule` - Time
- `person` - User
- `check_circle` - Success
- `verified_user` - Security
- `lock` - Secure
- `payment` / `payments` - Money
- `error` - Error
- `warning` - Warning
- `info` - Information
- `arrow_back` - Back
- `rocket_launch` - Next Steps
- `videocam` - Video Call
- `email` / `mark_email_read` - Email

Full list: https://fonts.google.com/icons
