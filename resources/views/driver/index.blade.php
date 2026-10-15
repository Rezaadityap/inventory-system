@extends('layouts.app')
@section('page-title')
    Pengemudi
@endsection
@section('page-subtitle')
    Kelola data dan status penugasan pengemudi
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, viewModal: false, editModal: false, editData: {}, deleteModal: false, deleteData: {} }">
        @livewire('driver-table')

        @include('driver.create')
        @include('driver.edit')
        @include('driver.delete')
    </div>
@endsection
