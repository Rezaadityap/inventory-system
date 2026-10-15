@extends('layouts.app')
@section('page-title')
    Pengguna
@endsection
@section('page-subtitle')
    Kelola akun, peran, dan hak akses pengguna
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, editModal: false, editData: {}, deleteModal: false, deleteData: {} }">
        @livewire('user-table')

        @include('user.create')
        @include('user.edit')
        @include('user.delete')
    </div>
@endsection
