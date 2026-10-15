@extends('layouts.app')
@section('page-title')
    Limbah
@endsection
@section('page-subtitle')
    Daftar seluruh limbah yang terdaftar
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, editModal: false, editData: {}, deleteModal: false, deleteData: {} }">
        @livewire('waste-table')

        @include('waste.create')
        @include('waste.edit')
        @include('waste.delete')
    </div>
@endsection
