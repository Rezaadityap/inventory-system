@extends('layouts.app')
@section('page-title')
    Customers
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
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
