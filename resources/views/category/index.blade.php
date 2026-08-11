@extends('layouts.app')
@section('page-title')
    Categories
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, editModal: false, editData: {}, deleteModal: false, deleteData: {} }">
        @livewire('category-table')

        @include('category.create')
        @include('category.edit')
        @include('category.delete')
    </div>
@endsection
