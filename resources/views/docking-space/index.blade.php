@extends('layouts.app')

@section('title', 'Manajemen Docking Space')
@section('body_title', 'Manajemen Docking Space')

@section('buttons_beside_title')
    <a href="{{ route('docking-space.index') }}" class="btn btn-outline-secondary">
        Reset Filter
    </a>
@endsection

@section('content')
    @include('partials.flash')

    @php
        $editing_space = $editingSpace ?? null;
        $statuses = [
            'active' => 'Aktif',
            'maintenance' => 'Pemeliharaan',
            'inactive' => 'Nonaktif',
        ];
    @endphp

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        {{ $editing_space ? 'Ubah Docking Space' : 'Tambah Docking Space' }}
                    </h3>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ $editing_space ? route('docking-space.update', $editing_space->id) : route('docking-space.store') }}">
                        @csrf
                        @if ($editing_space)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Nama Docking Space</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name', $editing_space->name ?? '') }}"
                                placeholder="Contoh: Dock 01"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi</label>
                            <input
                                type="text"
                                name="location"
                                class="form-control"
                                value="{{ old('location', $editing_space->location ?? '') }}"
                                placeholder="Contoh: Area Timur"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $editing_space->status ?? 'active') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Max Draft</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="max_draft"
                                    class="form-control"
                                    value="{{ old('max_draft', $editing_space->max_draft ?? '') }}"
                                >
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Max Length</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="max_length"
                                    class="form-control"
                                    value="{{ old('max_length', $editing_space->max_length ?? '') }}"
                                >
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Max Breadth</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="max_breadth"
                                    class="form-control"
                                    value="{{ old('max_breadth', $editing_space->max_breadth ?? '') }}"
                                >
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Max Width</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="max_width"
                                    class="form-control"
                                    value="{{ old('max_width', $editing_space->max_width ?? '') }}"
                                >
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Max Weight</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="max_weight"
                                    class="form-control"
                                    value="{{ old('max_weight', $editing_space->max_weight ?? '') }}"
                                >
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Max Capacity</label>
                                <input
                                    type="number"
                                    name="max_capacity"
                                    class="form-control"
                                    value="{{ old('max_capacity', $editing_space->max_capacity ?? '') }}"
                                >
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Catatan</label>
                            <textarea
                                name="notes"
                                class="form-control"
                                rows="3"
                                placeholder="Informasi khusus mengenai docking space ini"
                            >{{ old('notes', $editing_space->notes ?? '') }}</textarea>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ $editing_space ? 'Simpan Perubahan' : 'Simpan Docking Space' }}
                            </button>

                            @if ($editing_space)
                                <a href="{{ route('docking-space.index') }}" class="btn btn-outline-secondary">
                                    Batal
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Daftar Docking Space</h3>
                </div>

                <div class="card-body">
                    @if ($dockingSpaces->isEmpty())
                        <div class="text-center text-secondary py-4">
                            Belum ada docking space yang terdaftar.
                        </div>
                    @else
                        <div class="row row-cards">
                            @foreach ($dockingSpaces as $space)
                                @php
                                    $status_badge = match ($space->status) {
                                        'active' => 'bg-green-lt',
                                        'maintenance' => 'bg-yellow-lt',
                                        'inactive' => 'bg-red-lt',
                                        default => 'bg-secondary-lt',
                                    };
                                @endphp

                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h4 class="card-title mb-1">{{ $space->name }}</h4>
                                                    <div class="text-secondary small">{{ $space->location ?: 'Lokasi belum ditentukan' }}</div>
                                                </div>
                                                <span class="badge {{ $status_badge }}">
                                                    {{ strtoupper($space->status ?? 'inactive') }}
                                                </span>
                                            </div>

                                            <div class="mt-3 small text-secondary">
                                                <div class="d-flex justify-content-between">
                                                    <span>Max Draft</span>
                                                    <strong>{{ $space->max_draft ?? '-' }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Max Length</span>
                                                    <strong>{{ $space->max_length ?? '-' }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Max Breadth</span>
                                                    <strong>{{ $space->max_breadth ?? '-' }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Max Weight</span>
                                                    <strong>{{ $space->max_weight ?? '-' }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Max Capacity</span>
                                                    <strong>{{ $space->max_capacity ?? '-' }}</strong>
                                                </div>
                                            </div>

                                            @if (!empty($space->notes))
                                                <div class="mt-3">
                                                    <div class="text-secondary small mb-1">Catatan</div>
                                                    <div class="border rounded p-2 small">{{ $space->notes }}</div>
                                                </div>
                                            @endif

                                            <div class="mt-3 d-flex gap-2">
                                                <a href="{{ route('docking-space.edit', $space->id) }}" class="btn btn-sm btn-outline-primary">
                                                    Ubah
                                                </a>

                                                <form method="POST" action="{{ route('docking-space.destroy', $space->id) }}" onsubmit="return confirm('Hapus docking space ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
