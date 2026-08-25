@extends('layouts.app')
@section('page-title')
    History Stock
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ viewModal: false, viewData: {} }">
        @livewire('stock-history-table')

        @include('stock-history.view')
    </div>
@endsection
