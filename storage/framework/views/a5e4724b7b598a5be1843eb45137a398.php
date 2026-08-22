<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="<?php echo e(secure_asset('assets/img/favicon.ico')); ?>" type="image/x-icon">

	
    <title><?php echo $__env->yieldContent('title', 'SIREKA ASSI'); ?></title>

    
    <link href="<?php echo e(secure_asset('assets/dist/css/tabler.min.css?1692870487')); ?>" rel="stylesheet"/>
    <link href="<?php echo e(secure_asset('assets/dist/css/tabler-flags.min.css?1692870487')); ?>" rel="stylesheet"/>
    <link href="<?php echo e(secure_asset('assets/dist/css/tabler-payments.min.css?1692870487')); ?>" rel="stylesheet"/>
    <link href="<?php echo e(secure_asset('assets/dist/css/tabler-vendors.min.css?1692870487')); ?>" rel="stylesheet"/>
    <link href="<?php echo e(secure_asset('assets/dist/css/demo.min.css?1692870487')); ?>" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    
    <link href="<?php echo e(secure_asset('assets/dist/css/datatables.min.css')); ?>" rel="stylesheet"/>
    
    <link href="<?php echo e(secure_asset('assets/dist/css/select2.min.css')); ?>" rel="stylesheet"/>
    <link href="<?php echo e(secure_asset('assets/dist/css/select2-bootstrap-5-theme.min.css')); ?>" rel="stylesheet"/>
    
    <link href="<?php echo e(secure_asset('assets/dist/css/jquery-ui.min.css')); ?>" rel="stylesheet"/>
    <link href="<?php echo e(secure_asset('assets/dist/css/jquery-ui.structure.min.css')); ?>" rel="stylesheet"/>
    <link href="<?php echo e(secure_asset('assets/dist/css/jquery-ui.theme.min.css')); ?>" rel="stylesheet"/>

    
	<style>
        /* Font for the Web Page */
		@import url('https://rsms.me/inter/inter.css');
		:root {
			--tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
		}
		body {
			font-feature-settings: "cv03", "cv04", "cv11";
		}

        /* Font for Exporting PDF or Print Document */
		@font-face {
            font-family: 'Tahoma';
            src: url(<?php echo e(secure_asset('assets/fonts/tahoma.ttf')); ?>);
        }
        @font-face {
            font-family: 'Tahoma Bold';
            src: url(<?php echo e(secure_asset('assets/fonts/tahomabd.ttf')); ?>);
        }

        /* Override font size for Select2 container input field */
        .select2-container .select2-selection--single {
            font-size: 11pt !important;
        }

        /* Override font size for the dropdown options */
        .select2-container .select2-results__option {
            font-size: 10pt !important;  /* Set smaller font size for options */
        }

        /* Override font size for the search input field */
        .select2-container .select2-search__field {
            font-size: 10pt !important;  /* Set smaller font size for search input */
        }
	</style>
</head>
<body class="layout-fluid">
    <?php
        $unread_notifications = auth()->user()?->unreadNotifications ?? collect();
        $unread_notification_count = $unread_notifications->count();

        $employee_name = session('employee_name') ?? '-';
        $employee_position = session('employee_position');
        $employee_id = session('employee_id');
        $employee_summary = $employee_position && $employee_id
            ? $employee_position . ' - ' . $employee_id
            : '-';
    ?>

    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="<?php echo e(route('dashboard')); ?>">
                    <img src="<?php echo e(secure_asset('assets/img/assi_logo_with_name.png')); ?>" width="110" alt="Tabler" class="navbar-brand-image">
                </a>
            </h1>

            <div class="navbar-nav flex-row order-md-last">
                <div class="d-none d-md-flex">
                    <div class="nav-item dropdown d-none d-md-flex me-3">
                        
                        <?php if(auth()->guard()->check()): ?>
                        <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
                            <span class="badge bg-red" id="notification-badge" style="<?php echo e($unread_notification_count === 0 ? 'display: none;' : ''); ?>"><?php echo e($unread_notification_count); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Notifikasi Terbaru</h3>
                                </div>
                                <div class="list-group list-group-flush list-group-hoverable" id="notification-list">
                                    <?php $__empty_1 = true; $__currentLoopData = $unread_notifications->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-auto"><span class="status-dot status-dot-animated bg-red d-block"></span></div>
                                                <div class="col text-truncate">
                                                    <a href="<?php echo e($notification->data['url'] ?? '#'); ?>" class="text-body d-block"><?php echo e($notification->data['title']); ?></a>
                                                    <div class="d-block text-secondary text-truncate mt-n1">
                                                        <?php echo e($notification->data['message']); ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="list-group-item text-center text-muted" id="no-notifications-msg">
                                            Tidak ada notifikasi baru.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                        <span class="avatar avatar-sm" style="background-image: url(<?php echo e(secure_asset('assets/img/default_profile.jpg')); ?>)"></span>
                        <div class="d-none d-xl-block ps-2">
                            <div class="fw-bold"><?php echo e($employee_name); ?></div>
                            <div class="mt-1 small text-secondary">
                                <?php echo e($employee_summary); ?>

                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <a href="<?php echo e(route('settings.index')); ?>" class="dropdown-item">Pengaturan</a>
                        <a href="<?php echo e(route('logout')); ?>" class="dropdown-item">Keluar</a>
                    </div>
                </div>
            </div>

            
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                    <ul class="navbar-nav">
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-database">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" />
                                        <path d="M4 6v6a8 3 0 0 0 16 0v-6" />
                                        <path d="M4 12v6a8 3 0 0 0 16 0v-6" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    Database
                                </span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="dropdown-menu-columns">
                                    <div class="dropdown-menu-column">
                                            <h6 class="dropdown-header">Entitas Utama</h6>
                                            <a href="<?php echo e(route('company.index')); ?>" class="dropdown-item">Data Perusahaan</a>
                                            <a href="<?php echo e(route('ship.index')); ?>" class="dropdown-item">Data Kapal</a>
                                            <a href="<?php echo e(route('docking-space.index')); ?>" class="dropdown-item <?php echo e(request()->routeIs('docking-space.index') ? 'active' : ''); ?>">Data Dok</a>

                                            <div class="dropdown-divider"></div>

                                            <h6 class="dropdown-header">Referensi Kapal</h6>
                                            <a href="<?php echo e(route('ship-type.index')); ?>" class="dropdown-item">Data Jenis Kapal</a>
                                            <a href="<?php echo e(route('ship-classification.index')); ?>" class="dropdown-item">Data Klasifikasi</a>

                                            <div class="dropdown-divider"></div>

                                            <h6 class="dropdown-header">Lainnya</h6>
                                            <a href="<?php echo e(route('price-dictionary.index')); ?>" class="dropdown-item">Data Jenis Reparasi</a>

                                    </div>
                                </div>
                            </div>
                        </li>

                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-anchor">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M12 9v12m-8 -8a8 8 0 0 0 16 0m1 0h-2m-14 0h-2" />
                                        <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    Docking
                                </span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="dropdown-menu-columns">
                                    <div class="dropdown-menu-column">
                                            <h6 class="dropdown-header">Docking Request</h6>
                                            <a href="<?php echo e(route('docking-space-request.create')); ?>" class="dropdown-item <?php echo e(request()->routeIs('docking-space-request.create') ? 'active' : ''); ?>">Buat Docking Request</a>
                                            <a href="<?php echo e(route('docking-space-request.index')); ?>" class="dropdown-item <?php echo e(request()->routeIs('docking-space-request.index') ? 'active' : ''); ?>">Daftar Docking Request</a>

                                            <div class="dropdown-divider"></div>

                                            <h6 class="dropdown-header">Docking Schedule</h6>
                                            <a href="<?php echo e(route('docking-space-availability')); ?>" class="dropdown-item <?php echo e(request()->routeIs('docking-space-availability') ? 'active' : ''); ?>">Docking Schedule (Gantt)</a>

                                            <div class="dropdown-divider"></div>

                                            <h6 class="dropdown-header">Pelaksanaan</h6>
                                            <a href="<?php echo e(route('ship-docking.index.current')); ?>" class="dropdown-item <?php echo e(request()->routeIs('ship-docking.index.current') ? 'active' : ''); ?>">Docking Aktif</a>
                                            <a href="<?php echo e(route('ship-docking.history')); ?>" class="dropdown-item <?php echo e(request()->routeIs('ship-docking.history') ? 'active' : ''); ?>">Riwayat Docking</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-pencil">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                        <path d="M10 18l5 -5a1.414 1.414 0 0 0 -2 -2l-5 5v2h2z" />
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    Proyek
                                </span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="dropdown-menu-columns">
                                    <div class="dropdown-menu-column">
                                            <h6 class="dropdown-header">Manajemen Proyek</h6>
                                            <a href="<?php echo e(route('project.create')); ?>" class="dropdown-item">Buat Proyek Baru</a>
                                            <a href="<?php echo e(route('project.index')); ?>" class="dropdown-item">Daftar Proyek</a>
                                            <a href="<?php echo e(route('project.history')); ?>" class="dropdown-item">Riwayat Proyek</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        
                        
                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-users">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                                        </svg>
                                    </span>
                                    <span class="nav-link-title">
                                        Administrator
                                    </span>
                                </a>
                                <div class="dropdown-menu">
                                    <div class="dropdown-menu-columns">
                                        <div class="dropdown-menu-column">
                                            <h6 class="dropdown-header">Users</h6>
                                            <a href="<?php echo e(route('employee.create')); ?>" class="dropdown-item">Buat Akun Baru</a>
                                            <a href="<?php echo e(route('employee.index')); ?>" class="dropdown-item">Daftar User</a>

                                            <div class="dropdown-divider"></div>

                                            <h6 class="dropdown-header">Organization</h6>
                                            <a href="<?php echo e(route('organizational-unit.index')); ?>" class="dropdown-item">Data Unit Organisasi</a>
                                            <a href="<?php echo e(route('position.index')); ?>" class="dropdown-item">Data Jabatan</a>

                                            <div class="dropdown-divider"></div>

                                            <h6 class="dropdown-header">Roles & Permissions</h6>
                                            <a href="<?php echo e(route('role.index')); ?>" class="dropdown-item">Data Role</a>
                                            <a href="<?php echo e(route('permission.index')); ?>" class="dropdown-item">Data Permission</a>

                                            <div class="dropdown-divider"></div>

                                            <h6 class="dropdown-header">Pengaturan Sistem</h6>
                                            <a href="<?php echo e(route('notification-flag.index')); ?>" class="dropdown-item">Tipe Notifikasi (Flags)</a>
                                            <a href="<?php echo e(route('notification-settings.index')); ?>" class="dropdown-item">Pengaturan Notifikasi</a>
                                            <a href="<?php echo e(route('audit-log.index')); ?>" class="dropdown-item">Audit Log</a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    
    <div class="page-wrapper">
        
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <?php echo $__env->yieldContent('pre_body_title'); ?>
                        <h2 class="page-title"><?php echo $__env->yieldContent('body_title', 'Judul Halaman'); ?></h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <?php echo $__env->yieldContent('buttons_beside_title'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="page-body">
            <div class="container-xl">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>

        
        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center flex-row-reverse">
                    <div class="col-lg-auto ms-lg-auto">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">
                                <a href="https://www.instagram.com/adiluhung_shipyard" class="link-secondary" target="_blank" rel="noopener">
                                    Instagram
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="https://www.youtube.com/@assishipyard" class="link-secondary" target="_blank" rel="noopener">
                                    YouTube
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">
                                Copyright &copy; <?php echo e(date('Y')); ?>

                                <a href="https://www.assishipyard.com" class="link-secondary">
                                    PT. Adiluhung Saranasegara Indonesia
                                </a>
                            </li>
                            <li class="list-inline-item">
                                Sistem Informasi Reparasi Kapal (SIREKA)
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <?php echo $__env->yieldContent('modal'); ?>

    
    <script src="<?php echo e(secure_asset('assets/dist/js/tabler.min.js?1692870487')); ?>" defer></script>
    <script src="<?php echo e(secure_asset('assets/dist/js/demo.min.js?1692870487')); ?>" defer></script>
    <script src="<?php echo e(secure_asset('assets/dist/libs/list.js/dist/list.min.js?1692870487')); ?>" defer></script>

    
    <script src="<?php echo e(secure_asset('assets/dist/js/jquery-3.7.1.min.js')); ?>"></script>
    
    <script src="<?php echo e(secure_asset('assets/dist/js/jquery-validation/jquery.validate.min.js')); ?>"></script>
    <script src="<?php echo e(secure_asset('assets/dist/js/jquery-validation/additional-methods.min.js')); ?>"></script>

    
    <script src="<?php echo e(secure_asset('assets/dist/js/jquery-ui/jquery-ui.min.js')); ?>"></script>

    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/locale/id.min.js"></script>

    
    <script src="<?php echo e(secure_asset('assets/dist/js/datatables.min.js')); ?>"></script>
    <script src="<?php echo e(secure_asset('assets/dist/js/select2.full.min.js')); ?>"></script>

    <script>
        $('.dropdown-list').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true,
        });

        $('.dropdown-list').next('.select2-container').find('.select2-selection--single').css('font-size', '11pt');
        $('.dropdown-list').next('.select2-container').find('.select2-results__option').css('font-size', '11pt');
        $('.dropdown-list').next('.select2-container').find('.select2-search__field').css('font-size', '11pt');
    </script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script src="<?php echo e(secure_asset('assets/dist/libs/litepicker/dist/litepicker.js?1692870487')); ?>" defer></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $('#decimal-input').on('input', function () {
            const input = $(this).val().replace(/,/g, '');
            const number = parseFloat(input);

            if (!isNaN(number)) {
                $(this).val(number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const themeConfig = {
                theme: 'light',
                'theme-base': 'gray',
                'theme-font': 'sans-serif',
                'theme-primary': 'blue',
                'theme-radius': '1',
            };
            const url = new URL(window.location.href);
            const form = document.getElementById('offcanvasSettings');
            const resetButton = document.getElementById('reset-changes');

            if (form && resetButton) {
                    const checkItems = function () {
                        for (const key in themeConfig) {
                            const value = window.localStorage['tabler-' + key] || themeConfig[key];
                            if (value) {
                                const radios = form.querySelectorAll(`[name="${key}"]`);
                                if (radios) {
                                radios.forEach((radio) => {
                                        radio.checked = radio.value === value;
                                });
                            }
                        }
                    }
                    };
                    form.addEventListener('change', function (event) {
                        const { name, value } = event.target;
                        for (const key in themeConfig) {
                        if (name === key) {
                                document.documentElement.setAttribute('data-bs-' + key, value);
                                window.localStorage.setItem('tabler-' + key, value);
                            url.searchParams.set(key, value);
                        }
                    }
                        window.history.pushState({}, '', url);
                });
                    resetButton.addEventListener('click', function () {
                        for (const key in themeConfig) {
                            document.documentElement.removeAttribute('data-bs-' + key);
                            window.localStorage.removeItem('tabler-' + key);
                        url.searchParams.delete(key);
                    }
                    checkItems();
                        window.history.pushState({}, '', url);
                });
                    checkItems();
            }
        });
    </script>

    <?php if(auth()->guard()->check()): ?>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '<?php echo e(config('broadcasting.connections.reverb.key')); ?>',
            wsHost: window.location.hostname,
            wsPort: <?php echo e(config('broadcasting.connections.reverb.options.port', 80)); ?>,
            wssPort: <?php echo e(config('broadcasting.connections.reverb.options.port', 443)); ?>,
            forceTLS: (window.location.protocol === 'https:'),
            enabledTransports: ['ws', 'wss'],
        });

        window.Echo.private('App.Models.User.<?php echo e(auth()->user()->id); ?>')
            .notification((notification) => {
                $('#no-notifications-msg').remove();

                const badge = $('#notification-badge');
                const count = parseInt(badge.text() || 0);

                badge.text(count + 1);
                badge.show();

                const notificationHtml = `
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="status-dot status-dot-animated bg-red d-block"></span></div>
                            <div class="col text-truncate">
                                <a href="${notification.url ? notification.url : '#'}" class="text-body d-block">${notification.title}</a>
                                <div class="d-block text-secondary text-truncate mt-n1">
                                    ${notification.message}
                                </div>
                            </div>
                        </div>
                    </div>`;

                $('#notification-list').prepend(notificationHtml);
            });
    </script>
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\wamp64\www\assi-repair\resources\views\layouts\app.blade.php ENDPATH**/ ?>