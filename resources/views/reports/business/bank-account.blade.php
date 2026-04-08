@extends('layouts.app')

@section('title', 'گزارش تراکنش حساب بانکی — ' . config('app.name'))

@section('content')
@include('reports.business._bank-account-inner')
@include('reports.business._period-script')
@endsection
