@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('body_title', 'Data Karyawan')

@section('buttons_beside_title')
	<a href="{{ route('employee.create') }}" class="btn btn-primary"><i class="ti ti-user-plus me-1"></i>Tambah Karyawan</a>
@endsection

@section('content')
    @include('partials.flash')

	<style>
		.directory-toolbar {
			display: flex;
			flex-wrap: wrap;
			gap: 1rem;
			align-items: center;
			justify-content: space-between;
			padding: 1.25rem;
			border-radius: 1rem;
			background: linear-gradient(135deg, rgba(22, 101, 52, 0.08), rgba(74, 222, 128, 0.14));
			border: 1px solid rgba(34, 197, 94, 0.15);
		}

		.directory-search {
			position: relative;
			flex: 1 1 24rem;
		}

		.directory-search .ti {
			position: absolute;
			top: 50%;
			left: 1rem;
			transform: translateY(-50%);
			color: var(--tblr-secondary);
		}

		.directory-search-input {
			padding-left: 2.75rem;
			border-radius: 999px;
		}

		.directory-summary {
			display: flex;
			gap: .75rem;
			flex-wrap: wrap;
		}

		.directory-pill {
			min-width: 10rem;
			padding: .85rem 1rem;
			border-radius: .9rem;
			background-color: rgba(255, 255, 255, 0.72);
			border: 1px solid rgba(15, 23, 42, 0.06);
		}

		.directory-pill-label {
			display: block;
			font-size: .75rem;
			text-transform: uppercase;
			letter-spacing: .06em;
			color: var(--tblr-secondary);
		}

		.directory-pill-value {
			display: block;
			margin-top: .2rem;
			font-size: 1.4rem;
			font-weight: 700;
			color: var(--tblr-dark);
		}

		.directory-card {
			height: 100%;
			border: 1px solid rgba(15, 23, 42, 0.08);
			border-radius: 1rem;
			box-shadow: 0 1rem 2.5rem -1.75rem rgba(15, 23, 42, 0.45);
		}

		.directory-card .card-body {
			display: flex;
			flex-direction: column;
			gap: 1rem;
		}

		.directory-avatar {
			width: 3.25rem;
			height: 3.25rem;
			border-radius: 50%;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-size: 1rem;
			font-weight: 700;
			color: var(--tblr-green-fg);
			background: rgba(34, 197, 94, 0.12);
		}

		.directory-card-meta {
			display: grid;
			gap: .75rem;
		}

		.directory-card-meta-item {
			padding: .85rem 1rem;
			border-radius: .85rem;
			background-color: var(--tblr-bg-surface-secondary);
		}

		.directory-card-actions {
			display: flex;
			gap: .5rem;
			flex-wrap: wrap;
			margin-top: auto;
		}

		.directory-empty {
			display: none;
		}
	</style>

	<div class="d-flex flex-column gap-3">
		<div class="directory-toolbar">
			<div class="directory-search">
				<span class="ti ti-search"></span>
				<input type="search" class="form-control directory-search-input" id="employee-search" placeholder="Cari nama user, NIK, jabatan, atau unit..." autocomplete="off">
			</div>
			<div class="directory-summary">
				<div class="directory-pill">
					<span class="directory-pill-label">Total Karyawan</span>
					<span class="directory-pill-value">{{ $employees->count() }}</span>
				</div>
				<div class="directory-pill">
					<span class="directory-pill-label">Hasil Tampil</span>
					<span class="directory-pill-value" id="employee-visible-count">{{ $employees->count() }}</span>
				</div>
			</div>
		</div>

		<div class="row g-3" id="employee-card-list">
			@forelse ($employees as $employee)
				<div class="col-12 col-md-6 col-xl-4 employee-card-item" data-search="{{ strtolower(trim(implode(' ', array_filter([$employee->name, $employee->employee_id, $employee->position?->name, $employee->position?->organizational_unit?->name, $employee->status])))) }}">
					<div class="card directory-card">
						<div class="card-body">
							<div class="d-flex align-items-start justify-content-between gap-3">
								<div class="d-flex align-items-center gap-3">
									<span class="directory-avatar">{{ strtoupper(substr($employee->name, 0, 2)) }}</span>
									<div>
										<div class="text-secondary text-uppercase small fw-bold">Karyawan</div>
										<h3 class="card-title mb-1">{{ $employee->name }}</h3>
										<div class="text-secondary">NIK {{ $employee->employee_id }}</div>
									</div>
								</div>
								@if ($employee->status == 'active')
									<span class="badge bg-green-lt text-green">Aktif</span>
								@else
									<span class="badge bg-red-lt text-red">Tidak Aktif</span>
								@endif
							</div>

							<div class="directory-card-meta">
								<div class="directory-card-meta-item">
									<div class="text-secondary small mb-1">Jabatan</div>
									<div class="fw-semibold">{{ $employee->position?->name ?? '-' }}</div>
								</div>
								<div class="directory-card-meta-item">
									<div class="text-secondary small mb-1">Unit Organisasi</div>
									<div class="fw-semibold">{{ $employee->position?->organizational_unit?->name ?? '-' }}</div>
								</div>
							</div>

							<div class="directory-card-actions">
								<a href="{{ route('employee.show', $employee->unique_id ?? $employee->id) }}" class="btn btn-primary btn-sm" aria-label="Lihat detail {{ $employee->name }}"><i class="ti ti-eye"></i></a>
								<a href="{{ route('employee.edit', $employee->unique_id ?? $employee->id) }}" class="btn btn-outline-primary btn-sm" aria-label="Ubah {{ $employee->name }}"><i class="ti ti-pencil"></i></a>
							</div>
						</div>
					</div>
				</div>
			@empty
				<div class="col-12">
					<div class="card">
						<div class="card-body text-center text-secondary py-5">Belum ada data karyawan.</div>
					</div>
				</div>
			@endforelse
		</div>

		@if ($employees->isNotEmpty())
			<div class="card directory-empty" id="employee-empty-state">
				<div class="card-body text-center py-5">
					<div class="text-secondary">Tidak ada user yang cocok dengan pencarian.</div>
				</div>
			</div>
		@endif
	</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
			const $search_input = $('#employee-search');
			const $cards = $('.employee-card-item');
			const $empty_state = $('#employee-empty-state');
			const $visible_count = $('#employee-visible-count');

			$search_input.on('input', function() {
				const query = $(this).val().toString().trim().toLowerCase();
				let visible_total = 0;

				$cards.each(function() {
					const matches = $(this).data('search').toString().includes(query);
					$(this).toggle(matches);

					if (matches) {
						visible_total += 1;
					}
				});

				$visible_count.text(visible_total);
				$empty_state.toggle(visible_total === 0);
			});
        });
    </script>
@endpush
