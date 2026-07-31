@extends('layouts.app')

@section('title', 'Tambah Tipe Notifikasi - SIREKA')
@section('body_title', 'Tambah Tipe Notifikasi')

@section('content')
<div class="row row-cards">
    <div class="col-12 col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Tipe Notifikasi</h3>
                <div class="card-actions">
                    <a href="{{ route('notification-flag.index') }}" class="btn btn-white">
                        Batal
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form action="{{ route('notification-flag.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label required">Kode Event</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" placeholder="Contoh: project.created" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <small class="form-hint">Kode harus unik dan digunakan oleh developer di dalam kode (contoh: <code>invoice.paid</code>).</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Notifikasi</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Contoh: Proyek Baru Dibuat" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3" placeholder="Jelaskan kapan notifikasi ini akan muncul">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-footer text-end">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
