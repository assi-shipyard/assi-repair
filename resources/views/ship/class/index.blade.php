@extends('layouts.app')

@section('title', 'Data Klasifikasi')
@section('body_title', 'Data Klasifikasi')

@section('content')
    @include('partials.flash')

    <div class="row row-cards">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Tambah Klasifikasi</h3></div>
                <div class="card-body">
                    <form id="ship-class-form" action="{{ route('ship-classification.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label required">Nama Klasifikasi</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="abbreviation" class="form-label">Singkatan</label>
                            <input id="abbreviation" type="text" name="abbreviation" value="{{ old('abbreviation') }}" class="form-control @error('abbreviation') is-invalid @enderror" maxlength="255">
                            @error('abbreviation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Klasifikasi</h3><div class="card-actions text-secondary">{{ $ship_classes->count() }} data</div></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead><tr><th>Nama</th><th>Singkatan</th><th class="w-1">Aksi</th></tr></thead>
                        <tbody>
                            @forelse ($ship_classes as $ship_class)
                                <tr>
                                    <td>{{ $ship_class->name }}</td>
                                    <td>{{ $ship_class->abbreviation ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('ship-classification.destroy', $ship_class->id) }}" method="POST" onsubmit="return confirm('Hapus klasifikasi ini? Referensi klasifikasi pada data kapal terkait akan dikosongkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-secondary py-4">Belum ada klasifikasi kapal.</td></tr>
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
            jQuery('#ship-class-form').validate({
                rules: { name: { required: true, maxlength: 255 }, abbreviation: { maxlength: 255 } },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) { jQuery(element).addClass('is-invalid'); },
                unhighlight: function (element) { jQuery(element).removeClass('is-invalid'); }
            });
        }
    </script>
@endpush
