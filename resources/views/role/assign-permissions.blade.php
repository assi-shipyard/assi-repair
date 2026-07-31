@extends('layouts.app')

@section('title', 'Atur Permission Role')
@section('body_title', 'Atur Permission Role')

@section('buttons_beside_title')
    <a href="{{ route('role.show', $role->id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('role.update-permissions', $role->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row row-cards">
                @foreach ($permissions as $permission)
                    <div class="col-md-4">
                        <label class="form-check card card-body">
                            <input class="form-check-input" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked(in_array($permission->id, $assigned_permission_ids, true))>
                            <span class="form-check-label">{{ $permission->name }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
@endsection
