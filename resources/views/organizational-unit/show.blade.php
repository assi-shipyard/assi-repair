@extends('layouts.app')

@section('title', 'Detail Unit Organisasi')
@section('body_title', 'Detail Unit Organisasi')

@section('buttons_beside_title')
    <a href="{{ route('organizational-unit.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
    <a href="{{ route('organizational-unit.edit', $organizational_unit->unique_id ?? $organizational_unit->id) }}" class="btn btn-primary"><i class="ti ti-pencil me-1"></i>Ubah Unit</a>
@endsection

@section('content')
    @include('partials.flash')

    <div class="card unit-summary mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-auto"><span class="avatar avatar-lg bg-primary-lt text-primary"><i class="ti ti-building-community fs-2"></i></span></div>
                <div class="col"><div class="text-secondary small">{{ $organizational_unit->type_label }}</div><h2 class="mb-1">{{ $organizational_unit->name }}</h2><div class="text-secondary">Kode unit: <span class="font-monospace">{{ $organizational_unit->code ?? '-' }}</span></div></div>
                <div class="col-12 col-sm-auto d-flex gap-4"><div><div class="text-secondary small">Sub Unit</div><div class="h2 mb-0">{{ $organizational_unit->children->count() }}</div></div><div><div class="text-secondary small">Jabatan</div><div class="h2 mb-0">{{ $organizational_unit->positions->count() }}</div></div></div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-lg-5"><div class="card h-100"><div class="card-header"><h3 class="card-title">Struktur Organisasi</h3></div><div class="list-group list-group-flush"><div class="list-group-item"><div class="text-secondary small">Unit Induk</div><div class="fw-semibold mt-1">{{ $organizational_unit->parent?->name ?? 'Unit tingkat tertinggi' }}</div></div><div class="list-group-item"><div class="text-secondary small">Jenis Unit</div><div class="fw-semibold mt-1">{{ $organizational_unit->type_label }}</div></div></div></div></div>
        <div class="col-lg-7"><div class="card h-100"><div class="card-header"><h3 class="card-title">Sub Unit</h3></div><div class="list-group list-group-flush">@forelse ($organizational_unit->children as $child)<a href="{{ route('organizational-unit.show', $child->unique_id ?? $child->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"><span><span class="fw-semibold d-block">{{ $child->name }}</span><span class="text-secondary small">{{ $child->type_label }}</span></span><i class="ti ti-chevron-right text-secondary"></i></a>@empty<div class="list-group-item text-secondary">Belum ada sub unit.</div>@endforelse</div></div></div>
        <div class="col-12"><div class="card"><div class="card-header"><h3 class="card-title">Jabatan dan Karyawan</h3></div><div class="table-responsive"><table class="table table-vcenter card-table"><thead><tr><th>Jabatan</th><th class="text-end">Karyawan</th></tr></thead><tbody>@forelse ($organizational_unit->positions as $position)<tr><td class="fw-semibold">{{ $position->name }}</td><td class="text-end"><span class="badge bg-secondary-lt text-secondary">{{ $position->employees->count() }} karyawan</span></td></tr>@empty<tr><td colspan="2" class="text-center text-secondary py-4">Belum ada jabatan pada unit ini.</td></tr>@endforelse</tbody></table></div></div></div>
    </div>
@endsection

@push('styles')
    <style>.unit-summary { border-top: 3px solid var(--tblr-primary); }</style>
@endpush
