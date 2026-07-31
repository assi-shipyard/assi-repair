@extends('layouts.app')

@section('title', 'Atur Role Permission')
@section('body_title', 'Atur Role Permission')

@section('buttons_beside_title')
    <a href="{{ route('permission.show', $permission->id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')

    <form action="{{ route('permission.update-roles', $permission->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row row-cards">
                @foreach ($roles as $role)
                    <div class="col-md-4">
                        <label class="form-check card card-body">
                            <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id, $assigned_role_ids, true))>
                            <span class="form-check-label">{{ $role->name }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
@endsection
