@extends('layouts.app')

@section('title', 'گزارش بدهکار / بستانکار — ' . config('app.name'))

@section('content')
@include('reports.business._balances-inner')
@endsection
