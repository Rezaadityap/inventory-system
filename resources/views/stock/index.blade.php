@extends('layouts.app')
@section('page-title')
    Stok
@endsection
@section('page-subtitle')
    Kelola dan pantau ketersediaan stok barang
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, viewModal: false, viewData: {}, deleteModal: false, deleteData: {} }">
        @livewire('stock-table')

        @include('stock.create')
        @include('stock.view')
        @include('stock.delete')
    </div>
@endsection
