@extends('layouts.app')

@section('title', 'Ubah Karyawan')
@section('body_title', 'Ubah Karyawan')
@extends('layouts.app')

@section('title', 'Ubah Karyawan')
@section('body_title', 'Ubah Karyawan')

@section('buttons_beside_title')
    <a href="{{ route('employee.show', $employee->unique_id ?? $employee->id) }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')
    @include('partials.flash')
    @include('partials.form-shell-styles')

    @php
        $filled_employee_fields = collect([
            old('name', $employee->name),
            old('employee_id', $employee->employee_id),
            old('email', $employee->email),
            old('status', $employee->status),
            old('position_id', $employee->position_id),
            old('manager_id', $employee->direct_manager_employee_id),
            $employee->profile_photo_path,
        ])->filter()->count();
    @endphp

    <form action="{{ route('employee.update', $employee->unique_id ?? $employee->id) }}" method="POST" enctype="multipart/form-data" id="employee-form" class="form-shell">
        @csrf
        @method('PUT')
        <div class="form-layout">
            <div class="form-sidebar">
                <div class="form-info-card" style="--form-info-bg: var(--tblr-green-lt); --form-info-avatar-bg: var(--tblr-green);">
                    <div class="form-info-body">
                        <div class="form-info-header"><span class="form-info-avatar"><i class="ti ti-user-edit"></i></span><div><h2 class="form-info-title">Ubah user</h2><p class="form-info-subtitle">Pastikan identitas, status, dan struktur pelaporan user tetap akurat.</p></div></div>
                        <div class="form-step-list">
                            <div class="form-step-item"><span class="form-step-badge">1</span><span>Periksa NIK, email, dan status akun.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">2</span><span>Sesuaikan jabatan dan manajer langsung bila ada perubahan.</span></div>
                            <div class="form-step-item"><span class="form-step-badge">3</span><span>Ganti foto atau kata sandi hanya bila diperlukan.</span></div>
                        </div>
                        <div class="form-kpi-grid">
                            <div class="form-kpi"><span class="form-kpi-label">Field Terisi</span><span class="form-kpi-value">{{ $filled_employee_fields }}</span></div>
                            <div class="form-kpi"><span class="form-kpi-label">Status</span><span class="form-kpi-value">{{ $employee->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></div>
                            <div class="form-kpi"><span class="form-kpi-label">Foto</span><span class="form-kpi-value">{{ $employee->profile_photo_path ? 'Ada' : '-' }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title mb-0">Foto dan identitas visual</h3></div>
                    <div class="card-body">
                        <div class="form-preview-shell mb-4">
                            @if($employee->profile_photo_path)
                                <img id="photo-preview" src="{{ route('employee.photo', $employee->unique_id ?? $employee->id) }}" alt="Foto Profil" class="form-avatar-frame">
                                <span class="form-avatar-placeholder d-none" id="photo-preview-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user text-muted" width="72" height="72" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                </span>
                            @else
                                <img id="photo-preview" src="#" alt="Preview Foto Profil" class="form-avatar-frame d-none">
                                <span class="form-avatar-placeholder" id="photo-preview-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user text-muted" width="72" height="72" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                </span>
                            @endif
                            <div><h4 class="mb-1">Foto Profil</h4><div class="form-help-text">Maksimal 10MB dengan format JPG, JPEG, PNG, atau WEBP.</div></div>
                        </div>

                        <div class="w-100 mb-2"><label for="profile_photo" class="btn btn-outline-primary w-100">Ubah Foto</label><input class="form-control d-none" type="file" id="profile_photo" name="profile_photo" accept="image/*"></div>

                        @if($employee->profile_photo_path)
                            <div class="w-100 mb-4"><label class="form-check form-switch cursor-pointer justify-content-center"><input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="remove_photo"><span class="form-check-label text-danger">Hapus foto ini</span></label></div>
                        @endif

                        <div class="form-note-list">
                            <div class="form-note-item"><div class="form-note-label">NIK dan Akses</div><div>NIK tetap menjadi identitas inti meskipun user memiliki kata sandi yang diubah.</div></div>
                            <div class="form-note-item"><div class="form-note-label">Status Pengguna</div><div>Gunakan status nonaktif bila akun tidak lagi dipakai tetapi histori tetap harus tersimpan.</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-main-card card shadow-sm">
                <div class="form-main-card-header"><div><h3 class="form-main-card-title">Form User</h3><div class="form-main-card-copy">Perbarui data inti user, struktur organisasi, dan akses akun bila dibutuhkan.</div></div></div>
                <div class="form-main-card-body">
                    <div class="form-summary-alert mb-4"><div class="form-summary-top"><div><div class="form-summary-title">Ringkasan profil saat ini</div><div class="form-summary-copy">Fokus utama ada pada NIK, status, jabatan, dan jalur manajer.</div></div><span class="badge bg-green-lt text-green">{{ $employee->name }}</span></div></div>

                    <div class="form-surface mb-4">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Informasi Dasar</h3><div class="form-main-card-copy">Data inti user, status akun, dan posisi dalam organisasi.</div></div></div>
                        <div class="form-main-card-body">
                            <div class="row g-3">
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Nama Lengkap</label><input class="form-control" name="name" value="{{ old('name', $employee->name) }}" placeholder="Contoh: Budi Santoso" required></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">NIK</label><input class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" placeholder="Contoh: 123456789" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" maxlength="9"><div id="nik-feedback" class="form-text mt-2"></div></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $employee->email) }}" placeholder="Contoh: budi@contoh.com"></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Status</label><select class="form-select" name="status" required><option value="active" @selected(old('status', $employee->status) === 'active')>Aktif</option><option value="inactive" @selected(old('status', $employee->status) === 'inactive')>Tidak Aktif</option></select></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Jabatan</label><select class="form-select dropdown-list" name="position_id" required><option value="">Pilih jabatan</option>@foreach ($positions as $position)<option value="{{ $position->id }}" @selected(old('position_id', $employee->position_id) == $position->id)>{{ $position->name }}</option>@endforeach</select></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Manajer Langsung</label><select class="form-select dropdown-list" name="manager_id"><option value="">Tidak ada</option>@foreach ($managers as $manager)<option value="{{ $manager->id }}" @selected(old('manager_id', $employee->direct_manager_employee_id) == $manager->id)>{{ $manager->name }}</option>@endforeach</select></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-surface">
                        <div class="form-main-card-header"><div><h3 class="form-main-card-title">Akun Akses</h3><div class="form-main-card-copy">Ganti kata sandi hanya bila diperlukan dan biarkan kosong jika tidak ada perubahan.</div></div></div>
                        <div class="form-main-card-body">
                            <div class="row g-3">
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Kata Sandi Baru</label><input class="form-control" type="password" name="password" placeholder="Minimal 8 karakter (opsional)"><div class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah kata sandi.</div></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Konfirmasi Kata Sandi</label><input class="form-control" type="password" name="password_confirmation" placeholder="Ulangi kata sandi baru"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-card">
            <div class="form-actions-row">
                <div class="form-actions-copy">Simpan setelah NIK, status, jabatan, dan manajer langsung mencerminkan kondisi terbaru.</div>
                <div class="form-actions-buttons">
                    <a href="{{ route('employee.show', $employee->unique_id ?? $employee->id) }}" class="btn btn-outline-secondary">Batal</a>
                    <button class="btn btn-primary px-4" type="submit" id="btn-submit">Perbarui Karyawan</button>
                </div>
            </div>
        </div>
    </form>
@endsection
                        $('#remove_photo').prop('checked', false);
                    }
                    reader.readAsDataURL(file);
                }
            });

            $('#remove_photo').change(function() {
                if ($(this).is(':checked')) {
                    $('#profile_photo').val('');
                    $('#photo-preview').addClass('d-none');
                    $('#photo-preview-placeholder').removeClass('d-none');
                } else {
                    @if($employee->profile_photo_path)
                        $('#photo-preview').attr('src', '{{ route('employee.photo', $employee->unique_id ?? $employee->id) }}').removeClass('d-none');
                        $('#photo-preview-placeholder').addClass('d-none');
                    @endif
                }
            });

            // NIK Validation Logic
            let nikTimeout = null;
            $('#employee_id').on('input', function() {
                clearTimeout(nikTimeout);
                const nik = $(this).val();
                const feedback = $('#nik-feedback');

                if (nik.length < 5) {
                    feedback.html('');
                    $(this).removeClass('is-invalid is-valid');
                    return;
                }

                feedback.html('<span class="spinner-border spinner-border-sm text-secondary me-2" role="status"></span> Memeriksa NIK...');

                nikTimeout = setTimeout(() => {
                    $.post('{{ route('employee.check-nik') }}', {
                        _token: '{{ csrf_token() }}',
                        employee_id: nik,
                        exclude_id: {{ $employee->id }}
                    }).done(function(response) {
                        if (response.available) {
                            $('#employee_id').removeClass('is-invalid').addClass('is-valid');
                            feedback.html('<span class="text-success"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg> NIK tersedia</span>');
                        } else {
                            $('#employee_id').removeClass('is-valid').addClass('is-invalid');
                            feedback.html('<span class="text-danger"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-circle" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg> NIK sudah terdaftar</span>');
                        }
                    }).fail(function() {
                        feedback.html('<span class="text-muted">Gagal memeriksa NIK.</span>');
                    });
                }, 500);
            });

			$('#employee-form').validate({
                ignore: [],
				rules: {
					employee_id: {
						required: true,
						digits: true,
						minlength: 9,
						maxlength: 9
					},
					name: {
						required: true,
						minlength: 3,
						maxlength: 255
					},
					email: {
						email: true,
						maxlength: 255
					},
                    position_id: {
                        required: true
                    },
					password: {
						minlength: 8
					},
					password_confirmation: {
						equalTo: '[name="password"]'
					}
				},
				messages: {
                    employee_id: {
                        required: "Masukkan NIK karyawan.",
                        digits: "NIK harus berupa angka.",
                        minlength: "NIK harus 9 digit.",
                        maxlength: "NIK harus 9 digit."
                    },
                    name: {
                        required: "Masukkan nama karyawan.",
                        minlength: "Nama karyawan minimal 3 karakter.",
                        maxlength: "Nama karyawan maksimal 255 karakter."
                    },
                    email: {
                        email: "Masukkan email yang valid.",
                        maxlength: "Email maksimal 255 karakter."
                    },
                    position_id: {
                        required: 'Pilih jabatan karyawan.'
                    },
					password: {
						minlength: "Kata sandi minimal 8 karakter."
					},
					password_confirmation: {
						equalTo: "Konfirmasi kata sandi harus sama dengan kata sandi."
					}
                },
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    if (element.hasClass('select2-hidden-accessible')) {
                        error.insertAfter(element.next('.select2'));
                        return;
                    }

                    element.closest('.form-group').append(error);
                },
                highlight: function (element) {
                    const $element = $(element);
                    if ($element.hasClass('select2-hidden-accessible')) {
                        $element.next('.select2').find('.select2-selection').addClass('is-invalid');
                        return;
                    }

                    $element.addClass('is-invalid');
                },
                unhighlight: function (element) {
                    const $element = $(element);
                    if ($element.hasClass('select2-hidden-accessible')) {
                        $element.next('.select2').find('.select2-selection').removeClass('is-invalid');
                        return;
                    }

                    $element.removeClass('is-invalid');
                }
			});
		});
	</script>
@endpush
