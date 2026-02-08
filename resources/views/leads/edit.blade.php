@extends('layouts.app')

@section('title', 'ویرایش سرنخ — ' . config('app.name'))

@section('content')
    <h1 class="mb-8 text-xl font-semibold text-stone-800 sm:text-2xl">ویرایش سرنخ</h1>
    @include('leads._form', ['lead' => $lead, 'leadChannels' => $leadChannels])
@endsection
