@extends('layouts.app')
@section('page-title')
    Pembayaran
@endsection
@section('page-subtitle')
    Daftar dan status seluruh riwayat pembayaran
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ viewModal: false, viewData: { items: [] }, payModal: false, payData: {} }">
        @livewire('invoice-table')

        @include('invoice.view')
        @include('invoice.pay')
    </div>
@endsection
