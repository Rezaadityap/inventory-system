@extends('layouts.app')
@section('page-title')
    Roles
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, editModal: false, editData: {}, deleteModal: false, deleteData: {} }">
        @livewire('role-table')

        @include('role.create')
        @include('role.edit')
        @include('role.delete')
    </div>
@endsection
