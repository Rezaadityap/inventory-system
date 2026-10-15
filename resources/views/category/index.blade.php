@extends('layouts.app')
@section('page-title')
    Kategori
@endsection
@section('page-subtitle')
    Daftar seluruh kategori yang terdaftar
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, editModal: false, editData: {}, deleteModal: false, deleteData: {} }">
        @livewire('category-table')

        @include('category.create')
        @include('category.edit')
        @include('category.delete')
    </div>
@endsection
