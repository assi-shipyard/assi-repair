<form method="POST" action="{{ route($route_name, $docking_request->unique_id) }}" class="d-flex flex-column gap-2 js-review-form">
    @csrf
    <textarea class="form-control form-control-sm" name="notes" rows="2" maxlength="2000" placeholder="Catatan (wajib bila menolak)"></textarea>
    <button class="btn btn-sm btn-success w-100" type="submit" name="decision" value="approve">{{ $approve_label }}</button>
    <button class="btn btn-sm btn-outline-danger w-100" type="submit" name="decision" value="reject">Tolak</button>
</form>
