@extends('layouts.auth')

@section('title', 'Login - MeetFlow')

@section('content')
<div class="mb-10">
    <h2 class="font-display text-4xl font-semibold text-slate-900 dark:text-white mb-2">Welcome Back</h2>
    <p class="text-slate-500 dark:text-slate-400">Please enter your details to sign in to your account.</p>
</div>

@if(session('status'))
    <div class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300">
        {{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('login') }}" method="POST" class="space-y-6">
    @csrf
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="login">Email Address</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <span class="material-icons-outlined text-xl">mail</span>
            </span>
            <input
                class="block w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                id="login" name="login" placeholder="name@company.com" required type="text" value="{{ old('login') }}" />
        </div>
    </div>
    <div>
        <div class="flex justify-between items-center mb-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="password">Password</label>
            <a class="text-xs font-semibold text-primary hover:underline" href="{{ route('password.request') }}">Forgot password?</a>
        </div>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <span class="material-icons-outlined text-xl">lock</span>
            </span>
            <input
                class="block w-full pl-10 pr-12 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                id="password" name="password" placeholder="" required type="password" />
            <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                type="button" onclick="togglePassword()">
                <span class="material-icons-outlined text-xl" id="password-icon">visibility</span>
            </button>
        </div>
    </div>
    <div class="flex items-center">
        <input class="h-4 w-4 text-primary focus:ring-primary border-slate-300 rounded" id="remember" name="remember" type="checkbox" />
        <label class="ml-2 block text-sm text-slate-600 dark:text-slate-400" for="remember">
            Remember me for 30 days
        </label>
    </div>
    <button
        class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary/25 transform active:scale-[0.98]"
        type="submit">
        Sign In
    </button>
</form>

<div class="relative my-8">
    <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
    </div>
    <div class="relative flex justify-center text-sm">
        <span class="px-4 bg-background-light dark:bg-background-dark text-slate-500">Or continue with</span>
    </div>
</div>

<div class="grid grid-cols-1 gap-4">
    <button
        class="flex items-center justify-center gap-2 py-3 px-4 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors font-medium">
        <img alt="Google" class="w-5 h-5"
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAHiexu7A4DsB_wR5W10RKtGeyk42TookyjMNFUq9fdInsV9NDjZbay6pXj5RG4GZjsq7vM7qKjVhITD4d1gzHhrR8h4aZVeilr57RB8pY2FLJ0uoNroqPhaIMpAJ_iqfOU0-MzR44YJ6rG0JQ0-wAK6KjKzA6XD3KhuHLJ-NysipLQORGOeS988wmM1M6Rg2qM-aBvbHzss31CXmXH2yKGfhTWEbw0b-4tPjF7wbfd-_6pVkcvZ6LWiGfFXds56yhhHRurtIVxMgE" />
        <span>Continue with Google</span>
    </button>
</div>

<p class="mt-10 text-center text-slate-500 dark:text-slate-400">
    Don't have an account?
    <a class="font-semibold text-primary hover:underline" href="{{ route('register') }}">Create an account</a>
</p>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('password-icon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        passwordInput.type = 'password';
        icon.textContent = 'visibility';
    }
}
</script>
@endpush
