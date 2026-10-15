@extends('layouts.app')
@section('page-title')
    Pengiriman
@endsection
@section('page-subtitle')
    Pantau status dan alur pengiriman barang
@endsection

@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl" x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }}, viewModal: false, viewData: {}, editModal: false, editData: {}, deleteModal: false, deleteData: {}, shipModal: {{ $errors->has('photo') ? 'true' : 'false' }}, shipData: @js($errors->has('photo') ? ['id' => old('shipment_ref'), 'shipment_number' => old('shipment_ref_number')] : (object) []), arriveModal: false, arriveData: {}, receiveModal: false, receiveData: {} }">
        @livewire('shipment-table')

        @include('shipment.view')

        @role('admin')
            @include('shipment.create')
            @include('shipment.edit')
            @include('shipment.delete')
        @endrole

        @role('pengemudi')
            @include('shipment.ship')
            @include('shipment.arrive')
        @endrole

        @role('pelanggan')
            @include('shipment.receive')
        @endrole
    </div>

    @if (session('surat_jalan_url'))
        <script>
            window.open(@js(session('surat_jalan_url')), '_blank');
        </script>
    @endif
@endsection
