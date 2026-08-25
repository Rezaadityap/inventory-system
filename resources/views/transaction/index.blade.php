@extends('layouts.app')
@section('page-title')
    Transaksi
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl"
        x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, viewModal: false, viewData: {}, editModal: false, editData: { items: [] }, approveModal: false, approveData: {} }">
        @livewire('transaction-table')

        @role('pelanggan')
            @include('transaction.create')
            @include('transaction.edit')
        @endrole
        @role('admin')
            @include('transaction.approve')
        @endrole
        @include('transaction.view')
    </div>
@endsection
