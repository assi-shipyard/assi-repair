<div class="card mb-3"><div class="card-body">
    <form class="customer_form row g-2 align-items-end" method="GET" action="{{ route($directory_route) }}">
        <div class="col-md-7"><label for="search" class="form-label">Cari Pengguna</label><input id="search" name="search" class="form-control" maxlength="100" value="{{ $filters['search'] ?? '' }}" placeholder="{{ $search_hint }}"></div>
        <div class="col-md-3"><label for="filter_status" class="form-label">Status</label><select id="filter_status" name="status" class="form-select"><option value="">Semua Status</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Tidak Aktif</option></select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill" type="submit">Cari</button><a class="btn btn-outline-secondary" href="{{ route($directory_route) }}" aria-label="Reset pencarian"><i class="ti ti-refresh" aria-hidden="true"></i></a></div>
    </form>
</div></div>
