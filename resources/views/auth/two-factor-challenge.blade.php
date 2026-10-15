@extends('layouts.guest')

@section('content')
    <div class="flex flex-col items-center mb-6">
        <h1 class="text-base font-medium text-text-primary">Verifikasi Login</h1>
        <p class="text-sm text-text-secondary text-center">
            Kami mengirim kode OTP 6 digit ke <span class="font-medium text-text-primary">{{ $email }}</span>.
            Berlaku {{ $expiresInMinutes }} menit.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-success text-center">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}" class="flex flex-col gap-3.5">
        @csrf
        <div>
            <label for="code" class="block text-sm text-text-primary mb-1.5">Kode OTP</label>
            <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                maxlength="6" required autofocus placeholder="------"
                class="block w-full px-4 h-11 rounded-lg border-border text-lg text-center tracking-[0.6em] shadow-sm focus:border-primary focus:ring-primary">

            @error('code')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-text-secondary">
            <input type="checkbox" name="trust_device" value="1"
                class="rounded border-border text-primary focus:ring-primary">
            Percayai perangkat ini selama 30 hari
        </label>

        <button type="submit"
            class="w-full h-9 cursor-pointer bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg transition-colors mt-1">
            Verifikasi
        </button>
    </form>

    <form method="POST" action="{{ route('two-factor.resend') }}" class="mt-3 text-center">
        @csrf
        <button type="submit" class="text-xs cursor-pointer text-primary hover:text-primary-dark hover:underline">
            Tidak menerima kode? Kirim ulang
        </button>
    </form>

    <p class="text-center text-xs text-text-secondary mt-4">
        <a href="{{ route('login') }}" class="text-primary hover:text-primary-dark hover:underline">Kembali ke halaman
            login</a>
    </p>
@endsection
