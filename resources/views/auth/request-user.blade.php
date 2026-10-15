@extends('layouts.guest')

@section('content')
    <div class="flex flex-col items-center mb-6">
        <h1 class="text-base font-medium text-text-primary">Ajukan Akun Pelanggan</h1>
        <p class="text-sm text-text-secondary text-center">
            Lengkapi data perusahaan dan akun. Akun aktif setelah disetujui admin.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 text-sm text-danger text-center">
            Ada isian yang belum benar. Periksa kembali form di bawah.
        </div>
    @endif

    <form method="POST" action="{{ route('account-request.store') }}" enctype="multipart/form-data"
        class="flex flex-col gap-3.5">
        @csrf

        <p class="text-xs font-semibold text-text-secondary uppercase tracking-wide">Data Perusahaan</p>

        <div>
            <label for="customer_name" class="block text-sm text-text-primary mb-1.5">Nama Perusahaan / Customer</label>
            <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" required
                autofocus placeholder="Masukkan nama perusahaan"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">
            @error('customer_name')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone_number" class="block text-sm text-text-primary mb-1.5">No. HP</label>
            <input id="phone_number" type="text" name="phone_number" inputmode="numeric"
                value="{{ old('phone_number') }}" required placeholder="Contoh: 08123456789"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">
            @error('phone_number')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="address" class="block text-sm text-text-primary mb-1.5">Alamat Perusahaan</label>
            <textarea id="address" name="address" rows="3" required placeholder="Masukkan alamat perusahaan"
                class="block w-full px-4 py-2 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('address') }}</textarea>
            @error('address')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="company_photo" class="block text-sm text-text-primary mb-1.5">Foto Perusahaan
                <span class="text-text-secondary">(opsional)</span></label>
            <input id="company_photo" type="file" name="company_photo" accept="image/*"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm cursor-pointer focus:border-primary focus:ring-primary">
            @error('company_photo')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-xs font-semibold text-text-secondary uppercase tracking-wide mt-2">Data Akun</p>

        <div>
            <label for="name" class="block text-sm text-text-primary mb-1.5">Nama PIC</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required
                placeholder="Nama penanggung jawab"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">
            @error('name')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="username" class="block text-sm text-text-primary mb-1.5">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required
                placeholder="Masukkan username"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">
            @error('username')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm text-text-primary mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                placeholder="Masukkan email"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">
            @error('email')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm text-text-primary mb-1.5">Password</label>
            <input id="password" type="password" name="password" autocomplete="new-password" required
                placeholder="Minimal 8 karakter"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">
            @error('password')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm text-text-primary mb-1.5">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                required placeholder="Ulangi password"
                class="block w-full px-4 h-9 rounded-lg border-border text-sm shadow-sm focus:border-primary focus:ring-primary">
        </div>

        <button type="submit"
            class="w-full h-9 cursor-pointer bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg transition-colors mt-1">
            Kirim Permintaan
        </button>
    </form>

    <p class="text-center text-xs text-text-secondary mt-4">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-primary hover:text-primary-dark hover:underline">Masuk di sini</a>
    </p>
@endsection
