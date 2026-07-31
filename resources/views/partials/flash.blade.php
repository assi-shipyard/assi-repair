@if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
		<div class="alert-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon icon-2">
				<path d="M5 12l5 5l10 -10" />
			</svg>
		</div>
        <div>{{ session('success') }}</div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></a>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
		<div class="alert-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon icon-2">
				<path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
				<path d="M12 8v4" />
				<path d="M12 16h.01" />
			</svg>
		</div>
        <div>{{ session('error') }}</div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></a>
    </div>
@endif

@if ($errors->any())
	<div class="alert alert-danger alert-dismissible" role="alert">
		<div class="alert-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon icon-2">
				<path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
				<path d="M12 8v4" />
				<path d="M12 16h.01" />
			</svg>
		</div>
		<div>
			<h4 class="alert-heading">Kesalahan:</h4>
			<div class="alert-description">
				<ul class="alert-list">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		</div>
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
@endif
