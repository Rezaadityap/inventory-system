@extends('layouts.app')
@section('page-title')
    Pelanggan
@endsection
@section('page-subtitle')
    Kelola informasi dan data riwayat pelanggan
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, viewModal: false, viewData: {}, editModal: false, editData: {}, deleteModal: false, deleteData: {} }">
        @livewire('customer-table')

        @include('customer.create')
        @include('customer.view')
        @include('customer.edit')
        @include('customer.delete')
    </div>
@endsection
