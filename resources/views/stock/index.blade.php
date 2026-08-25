@extends('layouts.app')
@section('page-title')
    Stock
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl"
        x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, viewModal: false, viewData: {}, deleteModal: false, deleteData: {} }">
        @livewire('stock-table')

        @include('stock.create')
        @include('stock.view')
        @include('stock.delete')
    </div>
@endsection
