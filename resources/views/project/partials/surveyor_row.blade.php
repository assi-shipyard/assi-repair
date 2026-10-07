<div class="surveyor-row border rounded p-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-6 col-lg-3">
            <label class="form-label small mb-1">Nama</label>
            <input class="form-control" maxlength="150" name="owner_surveyors[{{ $index }}][name]" value="{{ $row['name'] ?? '' }}" placeholder="Nama surveyor">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label small mb-1">Perusahaan</label>
            <input class="form-control" maxlength="150" name="owner_surveyors[{{ $index }}][company]" value="{{ $row['company'] ?? '' }}" placeholder="Perusahaan">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label small mb-1">Jabatan</label>
            <input class="form-control" maxlength="150" name="owner_surveyors[{{ $index }}][position]" value="{{ $row['position'] ?? '' }}" placeholder="Jabatan">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label small mb-1">Email</label>
            <input class="form-control surveyor-email" type="email" maxlength="150" name="owner_surveyors[{{ $index }}][email]" value="{{ $row['email'] ?? '' }}" placeholder="Email">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label small mb-1">Telepon</label>
            <div class="input-group">
                <input class="form-control" maxlength="30" name="owner_surveyors[{{ $index }}][phone]" value="{{ $row['phone'] ?? '' }}" placeholder="Telepon">
                <button type="button" class="btn btn-icon btn-outline-danger remove-surveyor" aria-label="Hapus surveyor"><i class="ti ti-trash"></i></button>
            </div>
        </div>
    </div>
</div>
