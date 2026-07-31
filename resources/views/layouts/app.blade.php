<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="{{ secure_asset('assets/img/favicon.ico') }}" type="image/x-icon">

	{{-- Page Title --}}
	<title>@yield('title', 'Default Title')</title>

    {{-- Tabler Core CSS --}}
    <link href="{{ secure_asset('assets/dist/css/tabler.min.css?1692870487') }}" rel="stylesheet"/>
    <link href="{{ secure_asset('assets/dist/css/tabler-flags.min.css?1692870487') }}" rel="stylesheet"/>
    <link href="{{ secure_asset('assets/dist/css/tabler-payments.min.css?1692870487') }}" rel="stylesheet"/>
    <link href="{{ secure_asset('assets/dist/css/tabler-vendors.min.css?1692870487') }}" rel="stylesheet"/>
    <link href="{{ secure_asset('assets/dist/css/demo.min.css?1692870487') }}" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    {{-- DataTables CSS --}}
    <link href="{{ secure_asset('assets/dist/css/datatables.min.css') }}" rel="stylesheet"/>
    {{-- Select2 CSS --}}
    <link href="{{ secure_asset('assets/dist/css/select2.min.css') }}" rel="stylesheet"/>
    <link href="{{ secure_asset('assets/dist/css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet"/>
    {{-- jQuery UI CSS --}}
    <link href="{{ secure_asset('assets/dist/css/jquery-ui.min.css') }}" rel="stylesheet"/>
    <link href="{{ secure_asset('assets/dist/css/jquery-ui.structure.min.css') }}" rel="stylesheet"/>
    <link href="{{ secure_asset('assets/dist/css/jquery-ui.theme.min.css') }}" rel="stylesheet"/>

    {{-- Custom CSS --}}
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
            src: url({{ secure_asset('assets/fonts/tahoma.ttf') }});
        }
        @font-face {
            font-family: 'Tahoma Bold';
            src: url({{ secure_asset('assets/fonts/tahomabd.ttf') }});
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
    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
            {{-- Hamburger menu button --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Logo --}}
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ secure_asset('assets/img/assi_logo_with_name.png') }}" width="110" alt="Tabler" class="navbar-brand-image">
                </a>
            </h1>

            <div class="navbar-nav flex-row order-md-last">
                <div class="d-none d-md-flex">
                    <div class="nav-item dropdown d-none d-md-flex me-3">
                        {{-- Notifications --}}
						@auth
						@php
							$unreadNotifications = auth()->user()->unreadNotifications;
						@endphp
						<a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show notifications">
						  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
						  <span class="badge bg-red" id="notification-badge" style="{{ $unreadNotifications->count() == 0 ? 'display: none;' : '' }}">{{ $unreadNotifications->count() }}</span>
						</a>
						<div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
							<div class="card">
								<div class="card-header">
									<h3 class="card-title">Notifikasi Terbaru</h3>
								</div>
								<div class="list-group list-group-flush list-group-hoverable" id="notification-list">
									@forelse($unreadNotifications->take(5) as $notification)
									<div class="list-group-item">
										<div class="row align-items-center">
											<div class="col-auto"><span class="status-dot status-dot-animated bg-red d-block"></span></div>
											<div class="col text-truncate">
												<a href="{{ $notification->data['url'] ?? '#' }}" class="text-body d-block">{{ $notification->data['title'] }}</a>
												<div class="d-block text-secondary text-truncate mt-n1">
													{{ $notification->data['message'] }}
												</div>
											</div>
										</div>
									</div>
									@empty
									<div class="list-group-item text-center text-muted" id="no-notifications-msg">
										Tidak ada notifikasi baru.
									</div>
									@endforelse
								</div>
							</div>
						</div>
						@endauth
					</div>
                </div>

                {{-- User Profile --}}
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                        <span class="avatar avatar-sm" style="background-image: url({{ secure_asset('assets/img/default_profile.jpg') }})"></span>
                        <div class="d-none d-xl-block ps-2">
                            <div class="fw-bold">{{ session('employee_name') ?? "-" }}</div>
                            <div class="mt-1 small text-secondary">
                                {{ session('employee_position') ? session('employee_position') . " - " . session('employee_id') : "-" }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <a href="{{ route('settings.index') }}" class="dropdown-item">Pengaturan</a>
                        <a href="{{ route('logout') }}" class="dropdown-item">Keluar</a>
                    </div>
                </div>
            </div>

            {{-- Navbar Menu --}}
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                    <ul class="navbar-nav">
                        {{-- Data Compilations --}}
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
                                            <a href="{{ route('company.index') }}" class="dropdown-item">Data Perusahaan</a>
                                            <a href="{{ route('ship.index') }}" class="dropdown-item">Data Kapal</a>
                                            <a href="{{ route('dock.index') }}" class="dropdown-item">Data Dok</a>
                                            
                                            <div class="dropdown-divider"></div>
                                            
                                            <h6 class="dropdown-header">Referensi Kapal</h6>
                                            <a href="{{ route('ship-type.index') }}" class="dropdown-item">Data Jenis Kapal</a>
                                            <a href="{{ route('ship-classification.index') }}" class="dropdown-item">Data Klasifikasi</a>
                                            
                                            <div class="dropdown-divider"></div>
                                            
                                            <h6 class="dropdown-header">Lainnya</h6>
                                            <a href="{{ route('price-dictionary.index') }}" class="dropdown-item">Data Jenis Reparasi</a>

                                    </div>
                                </div>
                            </div>
                        </li>

                        {{-- Ship Docking --}}
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
                                            <h6 class="dropdown-header">Docking Space</h6>
                                            <a href="{{ route('docking-space-request.create') }}" class="dropdown-item">Form Permohonan Docking Space</a>
                                            <a href="{{ route('docking-space-request.index') }}" class="dropdown-item">Daftar Permohonan Docking Space</a>
                                            <a href="{{ route('docking-space-availability') }}" class="dropdown-item">Ketersediaan Docking Space</a>
                                            
                                            <div class="dropdown-divider"></div>
                                            
                                            <h6 class="dropdown-header">Pelaksanaan</h6>
                                            <a href="{{ route('ship-docking.index.current') }}" class="dropdown-item">Docking Kapal Sekarang</a>
                                            <a href="{{ route('ship-docking.history') }}" class="dropdown-item">Riwayat Docking Kapal</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        {{-- Project --}}
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
                                            <a href="{{ route('project.create') }}" class="dropdown-item">Buat Proyek Baru</a>
                                            <a href="{{ route('project.index') }}" class="dropdown-item">Daftar Proyek</a>
                                            <a href="{{ route('project.history') }}" class="dropdown-item">Riwayat Proyek</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        {{-- Administrator --}}
                        {{-- Disable this dropdown menu using Spatie and only 'admin' role can see it --}}
                        @role('admin')
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
                                            <a href="{{ route('employee.create') }}" class="dropdown-item">Buat Akun Baru</a>
                                            <a href="{{ route('employee.index') }}" class="dropdown-item">Daftar User</a>
                                            
                                            <div class="dropdown-divider"></div>
                                            
                                            <h6 class="dropdown-header">Organization</h6>
                                            <a href="{{ route('organizational-unit.index') }}" class="dropdown-item">Data Unit Organisasi</a>
                                            <a href="{{ route('position.index') }}" class="dropdown-item">Data Jabatan</a>
                                            
                                            <div class="dropdown-divider"></div>
                                            
                                            <h6 class="dropdown-header">Roles & Permissions</h6>
                                            <a href="{{ route('role.index') }}" class="dropdown-item">Data Role</a>
                                            <a href="{{ route('permission.index') }}" class="dropdown-item">Data Permission</a>

                                            <div class="dropdown-divider"></div>
                                            
                                            <h6 class="dropdown-header">Pengaturan Sistem</h6>
                                            <a href="{{ route('notification-flag.index') }}" class="dropdown-item">Tipe Notifikasi (Flags)</a>
                                            <a href="{{ route('notification-settings.index') }}" class="dropdown-item">Pengaturan Notifikasi</a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endrole
                    </ul>
                </div>
            </div>
        </div>
    </header>

    {{-- Page Body --}}
    <div class="page-wrapper">
        {{-- Page Body Header --}}
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        @yield('pre_body_title')
                        <h2 class="page-title">@yield('body_title', 'Page Title')</h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            @yield('buttons_beside_title')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Content --}}
        <div class="page-body">
            <div class="container-xl">
                @yield('content')
            </div>
        </div>

        {{-- Footer --}}
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
                                Copyright &copy; {{ date('Y') }}
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

    @yield('modal')

    {{-- Tabler Core and Libs JS--}}
    <script src="{{ secure_asset('assets/dist/js/tabler.min.js?1692870487') }}" defer></script>
    <script src="{{ secure_asset('assets/dist/js/demo.min.js?1692870487') }}" defer></script>
    <script src="{{ secure_asset('assets/dist/libs/list.js/dist/list.min.js?1692870487') }}" defer></script>
    {{-- jQuery --}}
    <script src="{{ secure_asset('assets/dist/js/jquery-3.7.1.min.js') }}"></script>
    {{-- jQuery Validation--}}
    <script src="{{ secure_asset('assets/dist/js/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ secure_asset('assets/dist/js/jquery-validation/additional-methods.min.js') }}"></script>
    {{-- jQuery UI 1.14 --}}
    <script src="{{ secure_asset('assets/dist/js/jquery-ui/jquery-ui.min.js') }}"></script>
    {{-- Vue 3 --}}
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    {{-- Popperjs --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    {{-- Tempus Dominus JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
    {{-- Axios --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    {{-- Echo and Pusher --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    {{-- Moment with Indonesian locale --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/locale/id.min.js"></script>
    {{-- DataTables --}}
    <script src="{{ secure_asset('assets/dist/js/datatables.min.js') }}"></script>
    {{-- Select2 --}}
    <script src="{{ secure_asset('assets/dist/js/select2.full.min.js') }}"></script>
    {{-- Enable Select2 --}}
    <script>
        $('.dropdown-list').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true
        });
        $('.dropdown-list').next('.select2-container').find('.select2-selection--single').css('font-size', '11pt'); // Input field
        $('.dropdown-list').next('.select2-container').find('.select2-results__option').css('font-size', '11pt');  // Dropdown options
        $('.dropdown-list').next('.select2-container').find('.select2-search__field').css('font-size', '11pt');  // Search input (if search is enabled)
    </script>
    {{-- Apex Charts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    {{-- Lite Picker --}}
    <script src="{{ secure_asset('assets/dist/libs/litepicker/dist/litepicker.js?1692870487') }}" defer></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Digit Groupping and Decimal Formatting --}}
    <script>
        $("#decimal-input").on("input", function() {
            let input = $(this).val().replace(/,/g, ""); // Remove existing commas
            let number = parseFloat(input);

            if (!isNaN(number)) {
                $(this).val(number.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
        });
    </script>
    {{-- Tabler Customize Theme --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var themeConfig = {
                theme: "light",
                "theme-base": "gray",
                "theme-font": "sans-serif",
                "theme-primary": "blue",
                "theme-radius": "1",
            };
            var url = new URL(window.location);
            var form = document.getElementById("offcanvasSettings");
            var resetButton = document.getElementById("reset-changes");
            
            if (form && resetButton) {
                var checkItems = function () {
                    for (var key in themeConfig) {
                        var value = window.localStorage["tabler-" + key] || themeConfig[key];
                        if (!!value) {
                            var radios = form.querySelectorAll(`[name="${key}"]`);
                            if (!!radios) {
                                radios.forEach((radio) => {
                                radio.checked = radio.value === value;
                                });
                            }
                        }
                    }
                };
                form.addEventListener("change", function (event) {
                    var target = event.target,
                        name = target.name,
                        value = target.value;
                    for (var key in themeConfig) {
                        if (name === key) {
                            document.documentElement.setAttribute("data-bs-" + key, value);
                            window.localStorage.setItem("tabler-" + key, value);
                            url.searchParams.set(key, value);
                        }
                    }
                    window.history.pushState({}, "", url);
                });
                resetButton.addEventListener("click", function () {
                    for (var key in themeConfig) {
                        var value = themeConfig[key];
                        document.documentElement.removeAttribute("data-bs-" + key);
                        window.localStorage.removeItem("tabler-" + key);
                        url.searchParams.delete(key);
                    }
                    checkItems();
                    window.history.pushState({}, "", url);
                });
                checkItems();
            }
        });
    </script>

    @auth
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('broadcasting.connections.reverb.key') }}',
            wsHost: window.location.hostname,
            wsPort: {{ config('broadcasting.connections.reverb.options.port', 80) }},
            wssPort: {{ config('broadcasting.connections.reverb.options.port', 443) }},
            forceTLS: (window.location.protocol === 'https:'),
            enabledTransports: ['ws', 'wss'],
        });

        window.Echo.private('App.Models.User.{{ auth()->user()->id }}')
            .notification((notification) => {
                // Remove 'no notifications' message if exists
                $('#no-notifications-msg').remove();

                // Increment Badge
                let badge = $('#notification-badge');
                let count = parseInt(badge.text() || 0);
                badge.text(count + 1);
                badge.show();

                // Prepend to list
                let notificationHtml = `
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
    @endauth

    @stack('scripts')
</body>
</html>
