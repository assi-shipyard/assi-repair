@extends('layouts.app')

@section('title', 'Tipe Notifikasi - SIREKA')
@section('body_title', 'Data Tipe Notifikasi (Flags)')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Tipe Notifikasi</h3>
                <div class="card-actions">
                    <a href="{{ route('notification-flag.create') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        Tambah Tipe
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Kode Event</th>
                            <th>Nama Notifikasi</th>
                            <th>Deskripsi</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($flags as $flag)
                        <tr>
                            <td><code>{{ $flag->code }}</code></td>
                            <td>{{ $flag->name }}</td>
                            <td class="text-muted">{{ $flag->description ?? '-' }}</td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('notification-flag.edit', $flag->id) }}" class="btn btn-white btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('notification-flag.destroy', $flag->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tipe notifikasi ini? Pengaturan yang terkait akan ikut terhapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada tipe notifikasi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
