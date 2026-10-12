@extends('layouts.app')
@section('title', 'Kelola Semua Akun')
@section('body_title', 'Kelola Semua Akun')
@section('buttons_beside_title')
    <a class="btn btn-outline-primary" href="{{ route('accounts.projects') }}">Akses &amp; Persetujuan Pelanggan</a>
    <a class="btn btn-primary" href="{{ route('employee.create') }}">Buat Akun Karyawan</a>
@endsection
@section('content')
    @include('partials.flash')
    <div class="card mb-3"><div class="card-body">
        <h3 class="card-title">Tiga Jenis Akun, Hak Akses Terpisah</h3>
        <p class="text-secondary mb-0">Admin menggunakan username. Karyawan menggunakan NIK 9 digit. Pelanggan menggunakan email dan wajib mengganti kata sandi awal. Perusahaan pelanggan dapat berupa pemilik kapal atau perusahaan surveyor; akses proyek tidak diberikan otomatis.</p>
    </div></div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Daftar Akun</h3></div>
        <div class="table-responsive"><table class="table table-vcenter card-table">
            <thead><tr><th>Nama / Identitas</th><th>Jenis Akun</th><th>Perusahaan</th><th>Status</th><th>Tindakan</th></tr></thead>
            <tbody>@forelse ($users as $user)
                <tr>
                    <td>{{ $user->name ?? $user->employee?->name ?? $user->username ?? $user->employee_id }}<div class="text-secondary small">{{ $user->is_customer() ? $user->email : ($user->username ?? $user->employee_id) }}</div></td>
                    <td>{{ ['admin' => 'Admin', 'internal' => 'Karyawan Internal', 'customer' => 'Pelanggan'][$user->account_type] }}</td>
                    <td>{{ $user->company?->name ?? '-' }}</td>
                    <td><span class="badge {{ $user->is_active ? 'bg-green-lt' : 'bg-red-lt' }}">{{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                    <td>
                        @if ($user->account_type !== 'internal')
                            <a class="btn btn-sm btn-outline-primary mb-1" href="{{ route('accounts.edit', $user) }}">Ubah</a>
                        @elseif ($user->employee)
                            <a class="btn btn-sm btn-outline-primary mb-1" href="{{ route('employee.edit', $user->employee) }}">Ubah Karyawan</a>
                        @endif
                        @if ($user->id !== auth()->id())
                        <form method="POST" action="{{ route('accounts.status', $user) }}">@csrf @method('PUT')<input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}"><button class="btn btn-sm btn-outline-secondary" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                    @else<span class="text-secondary small">Akun Anda</span>@endif</td>
                </tr>
            @empty<tr><td>Belum ada akun.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="card-footer">{{ $users->links() }}</div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Buat Akun Admin / Pelanggan</h3></div>
        <div class="card-body">
            <form class="customer_form" action="{{ route('accounts.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label for="account_type" class="form-label">Jenis Akun</label><select class="form-select" id="account_type" name="account_type" required><option value="customer" @selected(old('account_type', 'customer') === 'customer')>Pelanggan</option><option value="admin" @selected(old('account_type') === 'admin')>Admin</option></select></div>
                    <div class="col-md-6"><label for="name" class="form-label">Nama Lengkap</label><input id="name" name="name" class="form-control" required maxlength="255" value="{{ old('name') }}"></div>
                    <div class="col-md-6" id="username_group"><label for="username" class="form-label">Username Admin</label><input id="username" name="username" class="form-control" maxlength="64" value="{{ old('username') }}"></div>
                    <div class="col-md-6" id="company_group"><label for="company_id" class="form-label">Perusahaan Pelanggan / Surveyor</label><select id="company_id" name="company_id" class="form-select"><option value="">Pilih perusahaan</option>@foreach ($companies as $company)<option value="{{ $company->unique_id }}" @selected(old('company_id') === $company->unique_id)>{{ $company->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" required maxlength="64" value="{{ old('email') }}" autocomplete="off"></div>
                    <div class="col-md-6"><label for="password" class="form-label">Kata Sandi Awal</label><input id="password" name="password" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
                    <div class="col-md-6"><label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="12" maxlength="128" autocomplete="new-password"></div>
                </div>
                <p class="text-secondary mt-3">Minimal 12 karakter dengan huruf besar, huruf kecil, dan angka. Sampaikan kata sandi awal melalui saluran aman, bukan melalui halaman publik.</p>
                <button class="btn btn-primary" type="submit">Simpan Akun</button>
            </form>
        </div>
    </div>
    @include('customer.validation')
@endsection
@push('scripts')
<script>
    $(function () {
        function toggle_account_fields() {
            const is_customer = $('#account_type').val() === 'customer';
            $('#company_group').toggle(is_customer);
            $('#company_id').prop('required', is_customer).prop('disabled', !is_customer);
            $('#username_group').toggle(!is_customer);
            $('#username').prop('required', !is_customer).prop('disabled', is_customer);
        }
        $('#account_type').on('change', toggle_account_fields);
        toggle_account_fields();
    });
</script>
@endpush
