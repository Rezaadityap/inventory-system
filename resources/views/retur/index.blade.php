@extends('layouts.app')
@section('page-title')
    Retur
@endsection
@section('page-subtitle')
    Pantau dan kelola seluruh riwayat retur
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, viewModal: false, viewData: {}, approveModal: false, approveData: {}, rejectModal: false, rejectData: {}, settleModal: {{ $errors->has('refund_proof') ? 'true' : 'false' }}, settleData: @js($errors->has('refund_proof') ? array_merge(old('settle_ref', []), ['method' => 'refund', 'note' => old('note')]) : (object) []), cancelModal: false, cancelData: {} }">
        @livewire('retur-table')

        @role('pelanggan')
            @include('retur.create')
            @include('retur.cancel')
        @endrole
        @role('admin')
            @include('retur.approve')
            @include('retur.reject')
            @include('retur.settle')
        @endrole
        @include('retur.view')
    </div>
@endsection
