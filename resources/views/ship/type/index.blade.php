@extends('layouts.app')

@section('title', 'Data Jenis Kapal')
@section('body_title', 'Data Jenis Kapal')

@section('content')
    @include('partials.flash')

    <div class="row row-cards">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Tambah Jenis Kapal</h3></div>
                <div class="card-body">
                    <form id="ship-type-form" action="{{ route('ship-type.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label required">Nama Jenis Kapal</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Jenis Kapal</h3><div class="card-actions text-secondary">{{ $ship_types->count() }} data</div></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead><tr><th>Nama</th><th class="w-1">Aksi</th></tr></thead>
                        <tbody>
                            @forelse ($ship_types as $ship_type)
                                <tr>
                                    <td>{{ $ship_type->name }}</td>
                                    <td>
                                        <form action="{{ route('ship-type.destroy', $ship_type->id) }}" method="POST" onsubmit="return confirm('Hapus jenis kapal ini? Referensi jenis pada data kapal terkait akan dikosongkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-secondary py-4">Belum ada jenis kapal.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        if (window.jQuery && jQuery.fn.validate) {
            jQuery('#ship-type-form').validate({
                rules: { name: { required: true, maxlength: 255 } },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) { jQuery(element).addClass('is-invalid'); },
                unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); }
            });
        }
    </script>
@endpush
