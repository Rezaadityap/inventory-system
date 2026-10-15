@extends('layouts.guest')

@section('content')
    <div class="flex flex-col items-center mb-6">
        <h1 class="text-base font-medium text-text-primary">Sistem Inventory</h1>
        <p class="text-sm text-text-secondary">Masuk untuk kelola inventory kamu</p>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-success text-center">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-3.5">
        @csrf
        <div>
            <label for="username" class="block text-sm text-text-primary mb-1.5">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                autocomplete="username" placeholder="Masukkan username"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">

            @error('username')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm text-text-primary">Kata sandi</label>
                @if (Route::has('password.request'))
                    <a class="text-xs text-primary hover:text-primary-dark" href="{{ route('password.request') }}">
                        Lupa kata sandi?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                placeholder="Masukkan kata sandi"
                class="block w-full h-9 px-4 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">

            @error('password')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full h-9 cursor-pointer bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg transition-colors mt-1">
            Masuk
        </button>
    </form>

    <p class="text-center text-xs text-text-secondary mt-4">
        Belum punya akun?
        <a href="{{ route('account-request') }}" class="text-primary hover:text-primary-dark hover:underline">Ajukan
            akun</a>
    </p>
@endsection
