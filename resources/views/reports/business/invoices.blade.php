@extends('layouts.app')

@section('title', 'گزارش فاکتورها — ' . config('app.name'))

@section('content')
@include('reports.business._invoices-inner')
@include('reports.business._period-script')
@endsection
