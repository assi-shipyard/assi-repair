<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrganizationalUnitController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectJobDocumentPageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShipController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'login_process'])->name('login.process');
    Route::get('/admin/login', [LoginController::class, 'admin_login'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'admin_login_process'])->name('admin.login.process');
});

Route::get('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::controller(UserController::class)->prefix('settings')->name('settings.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::put('/profile', 'update_profile')->name('update-profile');
        Route::put('/password', 'update_password')->name('update-password');
        Route::delete('/photo', 'delete_photo')->name('delete-photo');
        Route::get('/photo', 'photo')->name('photo');
    });

    Route::controller(CompanyController::class)->prefix('company')->name('company.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/documents', 'show_documents')->name('documents');
        Route::post('/{id}/logo', 'upload_logo')->name('upload-logo');
        Route::post('/{id}/documents', 'upload_document')->name('upload-document');
        Route::delete('/{company_id}/documents/{document_id}', 'delete_document')->name('delete-document');
        Route::get('/{id}/data', 'get')->name('get');
    });

    Route::controller(ShipController::class)->prefix('ship')->name('ship.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');

        Route::get('/type', 'type_index')->name('type.index');
        Route::post('/type', 'type_store')->name('type.store');
        Route::delete('/type/{id}', 'type_destroy')->name('type.destroy');

        Route::get('/class', 'class_index')->name('class.index');
        Route::post('/class', 'class_store')->name('class.store');
        Route::delete('/class/{id}', 'class_destroy')->name('class.destroy');
    });

    Route::controller(EmployeeController::class)->prefix('employee')->name('employee.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/check-nik', 'check_nik')->name('check-nik');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/photo', 'photo')->name('photo');
    });

    Route::controller(OrganizationalUnitController::class)->prefix('organizational-unit')->name('organizational-unit.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::controller(PositionController::class)->prefix('position')->name('position.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::controller(RoleController::class)->prefix('role')->name('role.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/permissions', 'edit_permissions')->name('assign-permissions');
        Route::put('/{id}/permissions', 'update_permissions')->name('update-permissions');
    });

    Route::controller(PermissionController::class)->prefix('permission')->name('permission.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/roles', 'edit_roles')->name('assign-roles');
    });

    Route::resource('/notification-flag', \App\Http\Controllers\NotificationFlagController::class)->except(['show']);

    Route::controller(NotificationSettingController::class)->prefix('notification-settings')->name('notification-settings.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');
    });

    Route::controller(ProjectController::class)->prefix('project')->name('project.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/code-preview', 'generate_code_preview')->name('generate-code-preview');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/history', fn () => view('examples.placeholder', [
            'page_title' => 'Riwayat Proyek',
            'body_title' => 'Riwayat Proyek',
            'message' => 'Halaman riwayat proyek belum diaktifkan.',
        ]))->name('history');
    });

    Route::controller(ProjectJobDocumentPageController::class)->prefix('project/{projectId}/job-document')->name('project.job-document.workflow.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{documentId}', 'show')->name('show');
        Route::put('/{documentId}', 'update')->name('update');
        Route::delete('/{documentId}', 'destroy')->name('destroy');
        Route::post('/{documentId}/copy', 'copy')->name('copy');
        Route::post('/{documentId}/finalize-satisfaction-notes', 'finalizeSatisfactionNotes')->name('finalize-satisfaction-notes');
        Route::post('/{documentId}/jobs', 'storeJob')->name('job.store');
        Route::put('/{documentId}/jobs/{jobId}', 'updateJob')->name('job.update');
        Route::delete('/{documentId}/jobs/{jobId}', 'destroyJob')->name('job.destroy');
        Route::post('/{documentId}/jobs/{jobId}/materials', 'storeMaterial')->name('material.store');
        Route::put('/{documentId}/jobs/{jobId}/materials/{materialId}', 'updateMaterial')->name('material.update');
        Route::delete('/{documentId}/jobs/{jobId}/materials/{materialId}', 'destroyMaterial')->name('material.destroy');
        Route::get('/{documentId}/jobs/{jobId}/photos', 'photos')->name('job.photos');
        Route::post('/{documentId}/jobs/{jobId}/photos', 'storePhoto')->name('job.photo.store');
        Route::delete('/{documentId}/jobs/{jobId}/photos/{photoId}', 'destroyPhoto')->name('job.photo.destroy');
    });

    Route::view('/dock', 'examples.placeholder', [
        'page_title' => 'Data Dok',
        'body_title' => 'Data Dok',
        'message' => 'Halaman data dok belum diaktifkan.',
    ])->name('dock.index');

    Route::view('/ship-type', 'examples.placeholder', [
        'page_title' => 'Data Jenis Kapal',
        'body_title' => 'Data Jenis Kapal',
        'message' => 'Halaman data jenis kapal belum diaktifkan.',
    ])->name('ship-type.index');

    Route::view('/ship-classification', 'examples.placeholder', [
        'page_title' => 'Data Klasifikasi',
        'body_title' => 'Data Klasifikasi',
        'message' => 'Halaman data klasifikasi belum diaktifkan.',
    ])->name('ship-classification.index');

    Route::view('/price-dictionary', 'examples.placeholder', [
        'page_title' => 'Data Jenis Reparasi',
        'body_title' => 'Data Jenis Reparasi',
        'message' => 'Halaman data jenis reparasi belum diaktifkan.',
    ])->name('price-dictionary.index');

    Route::view('/docking-space-request/create', 'examples.placeholder', [
        'page_title' => 'Form Permohonan Docking Space',
        'body_title' => 'Form Permohonan Docking Space',
        'message' => 'Form permohonan docking space belum diaktifkan.',
    ])->name('docking-space-request.create');

    Route::view('/docking-space-request', 'examples.placeholder', [
        'page_title' => 'Daftar Permohonan Docking Space',
        'body_title' => 'Daftar Permohonan Docking Space',
        'message' => 'Daftar permohonan docking space belum diaktifkan.',
    ])->name('docking-space-request.index');

    Route::view('/docking-space-availability', 'examples.placeholder', [
        'page_title' => 'Ketersediaan Docking Space',
        'body_title' => 'Ketersediaan Docking Space',
        'message' => 'Halaman ketersediaan docking space belum diaktifkan.',
    ])->name('docking-space-availability');

    Route::view('/ship-docking/current', 'examples.placeholder', [
        'page_title' => 'Docking Kapal Sekarang',
        'body_title' => 'Docking Kapal Sekarang',
        'message' => 'Halaman docking kapal sekarang belum diaktifkan.',
    ])->name('ship-docking.index.current');

    Route::view('/ship-docking/history', 'examples.placeholder', [
        'page_title' => 'Riwayat Docking Kapal',
        'body_title' => 'Riwayat Docking Kapal',
        'message' => 'Halaman riwayat docking kapal belum diaktifkan.',
    ])->name('ship-docking.history');

    Route::view('/division', 'examples.placeholder', [
        'page_title' => 'Data Divisi',
        'body_title' => 'Data Divisi',
        'message' => 'Halaman data divisi belum diaktifkan.',
    ])->name('division.index');

    Route::view('/subdivision', 'examples.placeholder', [
        'page_title' => 'Data Subdivisi',
        'body_title' => 'Data Subdivisi',
        'message' => 'Halaman data subdivisi belum diaktifkan.',
    ])->name('subdivision.index');
});
