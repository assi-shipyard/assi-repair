@extends('layouts.app')

@section('title', $page_title ?? 'Halaman')
@section('body_title', $body_title ?? 'Halaman')

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body text-center py-5">
            <h3 class="mb-2">{{ $body_title ?? $page_title ?? 'Halaman' }}</h3>
            <p class="text-secondary mb-0">{{ $message ?? 'Halaman ini belum diaktifkan.' }}</p>
        </div>
    </div>
@endsection
