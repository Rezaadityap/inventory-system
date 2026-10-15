@extends('layouts.guest')

@section('content')
    <div class="flex flex-col items-center mb-6">
        <h1 class="text-base font-medium text-text-primary">Reset Password</h1>
        <p class="text-sm text-text-secondary text-center">Buat password baru untuk akun kamu</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-3.5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm text-text-primary mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required
                autofocus autocomplete="username" placeholder="Masukkan email"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">

            @error('email')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm text-text-primary mb-1.5">Password Baru</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                placeholder="Masukkan password baru"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">

            @error('password')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm text-text-primary mb-1.5">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                autocomplete="new-password" placeholder="Ulangi password baru"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">

            @error('password_confirmation')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full h-9 cursor-pointer bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg transition-colors mt-1">
            Reset Password
        </button>
    </form>

    <p class="text-center text-xs text-text-secondary mt-4">
        <a href="{{ route('login') }}" class="text-primary hover:text-primary-dark hover:underline">Kembali ke halaman
            login</a>
    </p>
@endsection
