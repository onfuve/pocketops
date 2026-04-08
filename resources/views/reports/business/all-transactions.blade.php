@extends('layouts.app')

@section('title', 'گزارش همه تراکنش‌ها — ' . config('app.name'))

@section('content')
@include('reports.business._all-transactions-inner')
@include('reports.business._period-script')
@endsection
