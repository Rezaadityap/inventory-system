@extends('layouts.app')
@section('page-title')
    Dashboard
@endsection
@section('page-subtitle')
    Welcome back, {{ Auth::user()->name }}
@endsection
@section('content')
    <div class="w-full bg-white p-5 shadow-md rounded-xl">
        Welcome, admin
    </div>
@endsection
