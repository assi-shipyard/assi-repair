@extends('layouts.app')

@section('title', 'Data Jenis Kapal')
@section('body_title', 'Data Jenis Kapal')

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Nama</th></tr></thead>
                    <tbody>
                        @forelse ($ship_types as $ship_type)
                            <tr><td>{{ $ship_type->name }}</td></tr>
                        @empty
                            <tr><td class="text-center text-secondary py-4">Belum ada jenis kapal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
