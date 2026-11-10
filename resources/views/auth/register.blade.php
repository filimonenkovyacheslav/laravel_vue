@extends('layouts.app')

@section('title')
	{{ __('auth.register') }} – {{ isset($siteName) ? $siteName : '' }}
@endsection

@section('content')

@include('partials.header')

@if (session('message'))
    <message-bar :message="{{ session('message') }}"></message-bar>
@endif

@if (session('errors'))
    <message-bar :errors="{{ session('errors') }}"></message-bar>
@endif

<register :roles="{{ $roles }}" :onlyrole="{{ json_encode($onlyrole) }}" :params="{{ $params }}" :olddata="{{ json_encode(session()->getOldInput()) }}"></register>

@endsection

@section('footer')
    @include('partials.footer')
@endsection
