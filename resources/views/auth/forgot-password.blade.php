@extends('layouts.guest')

@section('content')
    <div class="flex flex-col items-center mb-6">
        <h1 class="text-base font-medium text-text-primary">Lupa Password</h1>
        <p class="text-sm text-text-secondary text-center">Masukkan email kamu untuk mendapatkan akses reset password</p>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-success text-center">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-3.5">
        @csrf
        <div>
            <label for="email" class="block text-sm text-text-primary mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                autocomplete="email" placeholder="Masukkan email"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">

            @error('email')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full h-9 cursor-pointer bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg transition-colors mt-1">
            Kirim email verifikasi
        </button>
    </form>

    <p class="text-center text-xs text-text-secondary mt-4">
        <a href="{{ route('login') }}" class="text-primary hover:text-primary-dark hover:underline">Kembali ke halaman
            login</a>
    </p>
@endsection
