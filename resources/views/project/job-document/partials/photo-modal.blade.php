<div class="modal modal-blur fade" id="boq-photo-modal" tabindex="-1" aria-labelledby="boq-photo-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="boq-photo-modal-title">
                    <i class="ti ti-photo me-2 text-primary"></i>Foto Pekerjaan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3">
                {{-- Job Info Banner --}}
                <div class="alert alert-secondary py-2 mb-3 d-flex align-items-center">
                    <i class="ti ti-briefcase me-2 fs-2 text-secondary"></i>
                    <div class="min-w-0">
                        <div class="small text-secondary">Item Pekerjaan</div>
                        <strong id="boq-photo-job-name" class="text-reset text-break"></strong>
                    </div>
                </div>

                {{-- Empty State (No Photos) --}}
                <div id="boq-photo-empty" class="text-center py-5 border rounded bg-light-subtle d-none">
                    <div class="mb-2 text-secondary">
                        <i class="ti ti-photo-off" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-semibold text-secondary mb-1">Belum Ada Foto</h4>
                    <p class="text-secondary small mb-0">Belum ada foto yang diunggah untuk item pekerjaan ini.</p>
                </div>

                {{-- Carousel & Gallery Section --}}
                <div id="boq-photo-content">
                    {{-- Carousel --}}
                    <div class="position-relative rounded overflow-hidden mb-2 bg-dark">
                        <div id="boq-photo-carousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
                            <div class="carousel-inner" id="boq-photo-carousel-items" style="min-height: 280px; max-height: 420px; background: #0b1329;">
                                {{-- Slides dynamically inserted --}}
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#boq-photo-carousel" data-bs-slide="prev" id="boq-photo-prev-btn">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Sebelumnya</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#boq-photo-carousel" data-bs-slide="next" id="boq-photo-next-btn">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Selanjutnya</span>
                            </button>
                        </div>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-dark bg-opacity-75 text-white font-monospace" id="boq-photo-counter">1 / 1</span>
                        </div>
                    </div>

                    {{-- Active Photo Details & Actions Bar --}}
                    <div class="card card-sm mb-3 border">
                        <div class="card-body p-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span id="boq-photo-category-badge" class="badge bg-secondary-lt">Umum</span>
                                <span id="boq-photo-caption-text" class="small fw-semibold text-secondary"></span>
                            </div>
                            <div class="d-flex align-items-center flex-wrap gap-2 ms-auto">
                                <span id="boq-photo-time-text" class="text-secondary small"></span>
                                <a id="boq-photo-view-link" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary" title="Buka foto asli di tab baru">
                                    <i class="ti ti-external-link me-1"></i>Lihat Asli
                                </a>
                                @if ($can_edit)
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="boq-photo-delete-btn" title="Hapus foto ini">
                                        <i class="ti ti-trash me-1"></i>Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Thumbnail Gallery Preview --}}
                    <div class="mb-3">
                        <div class="small fw-semibold text-secondary mb-2 d-flex justify-content-between align-items-center">
                            <span><i class="ti ti-layout-grid me-1"></i>Galeri Foto (<span id="boq-photo-total-count">0</span>):</span>
                            <span class="small text-muted">Klik thumbnail untuk berpindah foto</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 overflow-auto p-1 border rounded bg-light-subtle" id="boq-photo-thumbnails" style="max-height: 140px;">
                            {{-- Thumbnails dynamically inserted --}}
                        </div>
                    </div>
                </div>

                {{-- Upload Section (Only when $can_edit is true) --}}
                @if ($can_edit)
                    <div class="card card-sm border" id="boq-photo-upload-section">
                        <div class="card-header py-2 bg-light-subtle">
                            <h4 class="card-title m-0 text-primary">
                                <i class="ti ti-upload me-1"></i>Unggah Foto Baru
                            </h4>
                        </div>
                        <div class="card-body p-3">
                            <form id="boq-photo-upload-form" method="POST" enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small required" for="boq-photo-file">Pilih Gambar</label>
                                        <input type="file" class="form-control form-control-sm" name="job_photo" id="boq-photo-file" accept="image/jpeg,image/png,image/webp" required>
                                        <div class="form-hint small">Format JPG, PNG, atau WebP (maksimal 4 MB).</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small" for="boq-photo-category">Kategori</label>
                                        <select class="form-select form-select-sm" name="photo_category" id="boq-photo-category">
                                            <option value="">Pilih Kategori (Opsional)</option>
                                            <option value="quotation">Penawaran / Pra-Pekerjaan</option>
                                            <option value="progress">Progres Pengerjaan</option>
                                            <option value="result_before">Kondisi Sebelum (Before)</option>
                                            <option value="result_after">Kondisi Sesudah (After)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label small" for="boq-photo-caption">Keterangan</label>
                                        <input type="text" class="form-control form-control-sm" name="caption" id="boq-photo-caption" placeholder="Keterangan singkat foto (opsional)" maxlength="191">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small" for="boq-photo-taken-at">Tanggal Pengambilan</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="taken_at" id="boq-photo-taken-at">
                                    </div>

                                    {{-- Client-side preview of selected file --}}
                                    <div class="col-12 d-none" id="boq-photo-file-preview-wrap">
                                        <div class="d-flex align-items-center gap-2 p-2 border rounded bg-light">
                                            <img id="boq-photo-file-preview-img" src="" alt="Pratinjau" class="rounded object-fit-cover" style="width: 48px; height: 48px;">
                                            <div class="min-w-0 flex-fill">
                                                <div class="small fw-semibold text-truncate" id="boq-photo-file-preview-name"></div>
                                                <div class="small text-secondary" id="boq-photo-file-preview-size"></div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-ghost-secondary btn-icon" id="boq-photo-file-preview-clear" title="Batalkan pilihan foto">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end mt-2">
                                        <button type="submit" class="btn btn-sm btn-primary" id="boq-photo-upload-submit">
                                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                                            <i class="ti ti-upload me-1"></i>Unggah Foto
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        let current_job_id = null;
        let current_job_name = '';
        let current_upload_url = '';
        let current_photos = [];
        let active_photo_index = 0;
        let photo_carousel_instance = null;

        function get_category_badge(category, label) {
            let badge_class = 'bg-secondary-lt text-secondary';
            if (category === 'quotation') {
                badge_class = 'bg-blue-lt text-blue';
            } else if (category === 'progress') {
                badge_class = 'bg-warning-lt text-warning';
            } else if (category === 'result_before') {
                badge_class = 'bg-purple-lt text-purple';
            } else if (category === 'result_after') {
                badge_class = 'bg-success-lt text-success';
            }

            return '<span class="badge ' + badge_class + '">' + (label || 'Umum') + '</span>';
        }

        function escape_html(text) {
            if (!text) return '';
            return $('<div>').text(text).html();
        }

        function reset_upload_form() {
            const form = document.getElementById('boq-photo-upload-form');
            if (form) {
                form.reset();
            }
            $('#boq-photo-file-preview-wrap').addClass('d-none');
            $('#boq-photo-file-preview-img').attr('src', '');
            $('#boq-photo-file-preview-name').text('');
            $('#boq-photo-file-preview-size').text('');
        }

        function update_active_photo_ui(idx) {
            if (!current_photos.length) return;

            idx = Math.max(0, Math.min(idx, current_photos.length - 1));
            active_photo_index = idx;
            const photo = current_photos[idx];

            $('#boq-photo-counter').text((idx + 1) + ' / ' + current_photos.length);
            $('#boq-photo-category-badge').replaceWith(
                $(get_category_badge(photo.category, photo.category_label)).attr('id', 'boq-photo-category-badge')
            );
            $('#boq-photo-caption-text').text(photo.caption || '-');
            $('#boq-photo-time-text').text(photo.taken_at || photo.created_at || '');
            $('#boq-photo-view-link').attr('href', photo.url);

            $('#boq-photo-delete-btn')
                .data('delete-url', photo.delete_url)
                .data('photo-id', photo.id);

            $('.boq-photo-thumb').each(function () {
                const is_active = parseInt($(this).data('index'), 10) === idx;
                $(this).css('border', is_active ? '2px solid var(--tblr-primary)' : '2px solid transparent');
            });
        }

        function render_photos(index_to_select) {
            index_to_select = index_to_select ?? 0;

            if (current_photos.length === 0) {
                $('#boq-photo-content').addClass('d-none');
                $('#boq-photo-empty').removeClass('d-none');
                return;
            }

            $('#boq-photo-empty').addClass('d-none');
            $('#boq-photo-content').removeClass('d-none');

            active_photo_index = Math.max(0, Math.min(index_to_select, current_photos.length - 1));
            $('#boq-photo-total-count').text(current_photos.length);

            const has_multiple = current_photos.length > 1;
            $('#boq-photo-prev-btn, #boq-photo-next-btn, #boq-photo-counter').toggleClass('d-none', !has_multiple);

            const $carousel_items = $('#boq-photo-carousel-items').empty();
            const $thumbnails = $('#boq-photo-thumbnails').empty();

            current_photos.forEach(function (photo, i) {
                const is_active = i === active_photo_index;

                // Carousel item
                const $item = $('<div class="carousel-item ' + (is_active ? 'active' : '') + '" data-index="' + i + '"></div>');
                const $frame = $('<div class="d-flex align-items-center justify-content-center" style="height: 380px; max-height: 50vh; background: #0b1329;"></div>');
                const $img = $('<img class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: contain;">')
                    .attr('src', photo.url)
                    .attr('alt', photo.caption || 'Foto pekerjaan');

                $frame.append($img);
                $item.append($frame);
                $carousel_items.append($item);

                // Thumbnail
                const $thumb = $('<div class="boq-photo-thumb rounded overflow-hidden flex-shrink-0" data-index="' + i + '"></div>')
                    .css({
                        'width': '72px',
                        'height': '56px',
                        'cursor': 'pointer',
                        'border': is_active ? '2px solid var(--tblr-primary)' : '2px solid transparent'
                    })
                    .attr('title', (photo.category_label || '') + (photo.caption ? ': ' + photo.caption : ''));

                const $thumb_img = $('<img class="w-100 h-100 object-fit-cover">').attr('src', photo.url).attr('alt', '');
                $thumb.append($thumb_img);
                $thumbnails.append($thumb);
            });

            update_active_photo_ui(active_photo_index);

            const carousel_el = document.getElementById('boq-photo-carousel');
            if (window.bootstrap && window.bootstrap.Carousel) {
                photo_carousel_instance = window.bootstrap.Carousel.getOrCreateInstance(carousel_el, {
                    interval: false,
                    keyboard: true
                });
                photo_carousel_instance.to(active_photo_index);
            }
        }

        function update_row_badge_and_dataset() {
            const json_str = JSON.stringify(current_photos);

            $(`.boq-job-photos-btn[data-job-id="${current_job_id}"]`).each(function () {
                this.dataset.photos = json_str;
            });

            const $wrapper = $(`.boq-job-photo-badge-wrapper[data-job-id="${current_job_id}"]`);
            if ($wrapper.length) {
                const count = current_photos.length;
                $wrapper.find('.boq-job-photo-count').text(count);
                const $btn = $wrapper.find('.boq-job-photos-btn');
                if (count > 0) {
                    $btn.removeClass('d-none');
                } else {
                    $btn.addClass('d-none');
                }
            }
        }

        // Carousel slide listener
        $('#boq-photo-carousel').on('slid.bs.carousel', function (e) {
            update_active_photo_ui(e.to);
        });

        // Thumbnail click listener
        $(document).on('click', '.boq-photo-thumb', function () {
            const idx = parseInt($(this).data('index'), 10);
            if (photo_carousel_instance) {
                photo_carousel_instance.to(idx);
            } else {
                update_active_photo_ui(idx);
            }
        });

        // Trigger button click
        $(document).on('click', '.boq-job-photos-btn', function (e) {
            e.preventDefault();
            current_job_id = this.dataset.jobId;
            current_job_name = this.dataset.jobName || '';
            current_upload_url = this.dataset.uploadUrl || '';

            try {
                current_photos = JSON.parse(this.dataset.photos || '[]');
            } catch (err) {
                current_photos = [];
            }

            $('#boq-photo-modal-title').html('<i class="ti ti-photo me-2 text-primary"></i>Foto Pekerjaan: ' + escape_html(current_job_name));
            $('#boq-photo-job-name').text(current_job_name);

            const form = document.getElementById('boq-photo-upload-form');
            if (form) {
                form.action = current_upload_url;
                reset_upload_form();
            }

            render_photos(0);
            $('#boq-photo-modal').modal('show');
        });

        // File input preview
        $('#boq-photo-file').on('change', function () {
            const file = this.files && this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#boq-photo-file-preview-img').attr('src', e.target.result);
                    $('#boq-photo-file-preview-name').text(file.name);
                    const size_kb = (file.size / 1024).toFixed(1);
                    $('#boq-photo-file-preview-size').text(size_kb + ' KB');
                    $('#boq-photo-file-preview-wrap').removeClass('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                $('#boq-photo-file-preview-wrap').addClass('d-none');
            }
        });

        $('#boq-photo-file-preview-clear').on('click', function () {
            reset_upload_form();
        });

        // Form submission via AJAX
        $('#boq-photo-upload-form').on('submit', function (e) {
            e.preventDefault();

            const file_input = document.getElementById('boq-photo-file');
            if (!file_input || !file_input.files || !file_input.files.length) {
                if (window.Swal) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih file foto terlebih dahulu.' });
                } else {
                    alert('Pilih file foto terlebih dahulu.');
                }
                return;
            }

            const file = file_input.files[0];
            if (file.size > 4 * 1024 * 1024) {
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: 'Ukuran file foto maksimal 4 MB.' });
                } else {
                    alert('Ukuran file foto maksimal 4 MB.');
                }
                return;
            }

            const $btn = $('#boq-photo-upload-submit');
            const $spinner = $btn.find('.spinner-border');
            $btn.prop('disabled', true);
            $spinner.removeClass('d-none');

            const form_data = new FormData(this);

            $.ajax({
                url: current_upload_url,
                method: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function (res) {
                    reset_upload_form();
                    if (res.photo) {
                        current_photos.push(res.photo);
                        update_row_badge_and_dataset();
                        render_photos(current_photos.length - 1);
                    }
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: res.message || 'Foto berhasil diunggah.',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal mengunggah foto. Pastikan format dan ukuran sesuai.';
                    if (window.Swal) {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    } else {
                        alert(msg);
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $spinner.addClass('d-none');
                }
            });
        });

        // Photo delete via AJAX
        $('#boq-photo-delete-btn').on('click', function () {
            const photo = current_photos[active_photo_index];
            if (!photo || !photo.delete_url) return;

            const execute_delete = function () {
                $.ajax({
                    url: photo.delete_url,
                    method: 'POST',
                    data: { _method: 'DELETE' },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function (res) {
                        current_photos.splice(active_photo_index, 1);
                        active_photo_index = Math.max(0, active_photo_index - 1);
                        update_row_badge_and_dataset();
                        render_photos(active_photo_index);

                        if (window.Swal) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: res.message || 'Foto berhasil dihapus.',
                                showConfirmButton: false,
                                timer: 2500
                            });
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menghapus foto.';
                        if (window.Swal) {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                        } else {
                            alert(msg);
                        }
                    }
                });
            };

            if (window.Swal) {
                Swal.fire({
                    title: 'Hapus foto ini?',
                    text: 'Foto yang dihapus tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d63939'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        execute_delete();
                    }
                });
            } else if (confirm('Hapus foto ini?')) {
                execute_delete();
            }
        });
    })();
</script>
@endpush
