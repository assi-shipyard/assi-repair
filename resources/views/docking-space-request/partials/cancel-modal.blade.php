<div class="modal modal-blur fade" id="cancel-request-modal" tabindex="-1" aria-labelledby="cancel-request-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="" class="modal-content" id="cancel-request-form">
            @csrf
            <input type="hidden" name="cancel_request_id" value="">
            <div class="modal-status bg-danger"></div>
            <div class="modal-header">
                <h5 class="modal-title" id="cancel-request-modal-title">Batalkan permohonan docking?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-3 small">
                    <dt class="col-5 col-sm-4 text-secondary fw-normal">Kapal</dt>
                    <dd class="col-7 col-sm-8 fw-medium" data-field="ship">-</dd>
                    <dt class="col-5 col-sm-4 text-secondary fw-normal">Proyek</dt>
                    <dd class="col-7 col-sm-8" data-field="project">-</dd>
                    <dt class="col-5 col-sm-4 text-secondary fw-normal">Jadwal</dt>
                    <dd class="col-7 col-sm-8" data-field="schedule">-</dd>
                    <dt class="col-5 col-sm-4 text-secondary fw-normal">Docking space</dt>
                    <dd class="col-7 col-sm-8" data-field="dock">-</dd>
                    <dt class="col-5 col-sm-4 text-secondary fw-normal">Status</dt>
                    <dd class="col-7 col-sm-8 mb-0" data-field="status">-</dd>
                </dl>

                <div class="alert alert-warning mb-3" role="alert">
                    <div>
                        <div class="fw-medium">Tindakan ini tidak dapat dibatalkan.</div>
                        <div class="text-secondary small mt-1">
                            Permohonan akan berstatus <strong>Dibatalkan</strong> dan dokumen yang belum masuk folder proyek dipindahkan ke arsip pembatalan.
                            <span class="d-none" data-field="approved-note">Slot docking yang sudah dijadwalkan juga akan dilepas sehingga dapat dipakai kapal lain.</span>
                        </div>
                    </div>
                </div>

                <label class="form-label required" for="cancellation_reason">Alasan pembatalan</label>
                <textarea class="form-control @error('cancellation_reason') is-invalid @enderror" id="cancellation_reason" name="cancellation_reason" rows="3" maxlength="2000" placeholder="Contoh: Owner membatalkan rencana docking karena jadwal pelayaran berubah.">{{ old('cancellation_reason') }}</textarea>
                @error('cancellation_reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Kembali</button>
                <button type="submit" class="btn btn-danger"><i class="ti ti-x me-1"></i>Ya, batalkan permohonan</button>
            </div>
        </form>
    </div>
</div>
