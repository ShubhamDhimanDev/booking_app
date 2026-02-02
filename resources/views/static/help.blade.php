@extends('layouts.app')

@section('title', 'Help Center')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="glass-card p-8 rounded-2xl">
            <h1 class="text-2xl font-bold mb-4">Help Center</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">Fill out the form below and we'll get back to you. This is a static form—make it dynamic later as needed.</p>

            <form method="POST" action="{{ route('help.submit') }}" class="space-y-4">
                @csrf

                @if(session('status'))
                    <div class="p-3 rounded-md bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200">
                        {{ session('status') }}
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3" placeholder="Your name" />
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3" placeholder="you@example.com" />
                    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="mt-1 block w-full rounded-md border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3" placeholder="How can we help?" />
                    @error('subject') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Message</label>
                    <textarea name="message" rows="6" class="mt-1 block w-full rounded-md border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-3" placeholder="Your message...">{{ old('message') }}</textarea>
                    @error('message') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-semibold">Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection
