@extends('layouts.app')

@section('title', 'Data Kelas Kapal')
@section('body_title', 'Data Kelas Kapal')

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Nama</th><th>Singkatan</th></tr></thead>
                    <tbody>
                        @forelse ($ship_classes as $ship_class)
                            <tr><td>{{ $ship_class->name }}</td><td>{{ $ship_class->abbreviation ?? '-' }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-secondary py-4">Belum ada kelas kapal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
