@extends('layouts.app')

@section('title', 'Ubah Jabatan')
@section('body_title', 'Ubah Jabatan')

@section('buttons_beside_title')
    <a href="{{ route('position.show', $position->id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('position.update', $position->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="name" value="{{ old('name', $position->name) }}" required></div>
                <div class="col-md-6"><label class="form-label">Kode</label><input class="form-control" name="code" value="{{ old('code', $position->code ?? '') }}"></div>
                <div class="col-md-6"><label class="form-label">Kategori</label><select class="form-select" name="category" required>@foreach ($category_options as $category => $label)<option value="{{ $category }}" @selected(old('category', $position->category) === $category)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Unit Organisasi</label><select class="form-select" name="organizational_unit_id" required>@foreach ($organizational_units as $organizational_unit)<option value="{{ $organizational_unit->id }}" @selected(old('organizational_unit_id', $position->organizational_unit_id) == $organizational_unit->id)>{{ $organizational_unit->name }}</option>@endforeach</select></div>
                <div class="col-md-6 form-check mt-4"><input class="form-check-input" type="checkbox" name="is_head_position" value="1" id="is_head_position" @checked(old('is_head_position', $position->is_head_position))><label class="form-check-label" for="is_head_position">Jabatan kepala</label></div>
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Perbarui</button></div>
    </form>
@endsection
