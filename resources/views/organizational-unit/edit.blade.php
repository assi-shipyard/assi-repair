@extends('layouts.app')

@section('title', 'Ubah Unit Organisasi')
@section('body_title', 'Ubah Unit Organisasi')

@section('buttons_beside_title')
    <a href="{{ route('organizational-unit.show', $organizational_unit->unique_id ?? $organizational_unit->id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('organizational-unit.update', $organizational_unit->unique_id ?? $organizational_unit->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="{{ old('name', $organizational_unit->name) }}" required></div>
                <div class="col-md-6"><label class="form-label">Kode</label><input class="form-control" name="code" value="{{ old('code', $organizational_unit->code ?? '') }}"></div>
                <div class="col-md-6"><label class="form-label">Jenis</label><select class="form-select" name="type" required>@foreach ($types as $type => $label)<option value="{{ $type }}" @selected(old('type', $organizational_unit->type) === $type)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Unit Induk</label><select class="form-select" name="parent_id"><option value="">Tanpa induk</option>@foreach ($organizationalUnits as $organizationalUnitOption)<option value="{{ $organizationalUnitOption->id }}" @selected(old('parent_id', $organizational_unit->parent_id) == $organizationalUnitOption->id)>{{ $organizationalUnitOption->name }}</option>@endforeach</select></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
@endsection
