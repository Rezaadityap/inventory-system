@extends('layouts.app')
@section('page-title')
    Transaksi
@endsection
@section('page-subtitle')
    Pantau dan kelola seluruh riwayat transaksi
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, viewModal: false, viewData: {}, editModal: false, editData: { items: [] }, approveModal: false, approveData: {}, rejectModal: false, rejectData: {}, cancelModal: false, cancelData: {} }">
        @livewire('transaction-table')

        @role('pelanggan')
            @include('transaction.create')
            @include('transaction.cancel')
            @include('transaction.edit')
        @endrole
        @role('admin')
            @include('transaction.approve')
            @include('transaction.reject')
        @endrole
        @include('transaction.view')
    </div>
@endsection
