@extends('layouts.app')

@section('title', 'Pengaturan Notifikasi - SIREKA')
@section('body_title', 'Pengaturan Notifikasi')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <form action="{{ route('notification-settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                @foreach($flags as $flag)
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">{{ $flag->name }}</h3>
                            <div class="card-actions">
                                <span class="badge bg-blue-lt">{{ $flag->code }}</span>
                            </div>
                        </div>
                        <div class="card-body overflow-auto" style="max-height: 400px;">
                            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                @foreach($employees as $employee)
                                    @php
                                        $isChecked = false;
                                        if (isset($settings[$flag->id])) {
                                            $isChecked = $settings[$flag->id]->contains('user_id', $employee->user_id);
                                        }
                                    @endphp
                                    <label class="form-selectgroup-item flex-fill mb-2">
                                        <input type="checkbox" name="settings[{{ $flag->id }}][]" value="{{ $employee->user_id }}" class="form-selectgroup-input" {{ $isChecked ? 'checked' : '' }}>
                                        <div class="form-selectgroup-label d-flex align-items-center p-3">
                                            <div class="me-3">
                                                <span class="form-selectgroup-check"></span>
                                            </div>
                                            <div>
                                                <div class="font-weight-medium">{{ $employee->name }}</div>
                                                <div class="text-muted small">{{ $employee->position?->name ?? 'Tanpa Jabatan' }} - {{ $employee->position?->organizational_unit?->name ?? 'Tanpa Unit' }}</div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
