<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrganizationalUnitController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\DockingApprovalController;
use App\Http\Controllers\DockingManagementController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectJobDocumentPageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShipController;
use App\Http\Controllers\ShipDocumentController;
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

Route::middleware(['auth', 'audit_log'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin')->prefix('audit-log')->name('audit-log.')->group(function (): void {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
    });

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
        Route::get('/{company}', 'show')->name('show');
        Route::get('/{company}/edit', 'edit')->name('edit');
        Route::put('/{company}', 'update')->name('update');
        Route::delete('/{company}', 'destroy')->name('destroy');
        Route::get('/{company}/documents', 'show_documents')->name('documents');
        Route::post('/{company}/logo', 'upload_logo')->name('upload-logo');
        Route::post('/{company}/documents', 'upload_document')->name('upload-document');
        Route::delete('/{company}/documents/{document}', 'delete_document')->name('delete-document');
        Route::get('/{company}/data', 'get')->name('get');
    });

    Route::controller(ShipController::class)->prefix('ship')->name('ship.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{ship}', 'show')->name('show');
        Route::get('/{ship}/edit', 'edit')->name('edit');
        Route::put('/{ship}', 'update')->name('update');
        Route::delete('/{ship}', 'destroy')->name('destroy');

        Route::post('/{ship}/documents', [ShipDocumentController::class, 'store'])->name('documents.store');
        Route::get('/{ship}/documents/{document}', [ShipDocumentController::class, 'download'])->name('documents.download');
        Route::delete('/{ship}/documents/{document}', [ShipDocumentController::class, 'destroy'])->name('documents.destroy');

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
        Route::get('/{employee}', 'show')->name('show');
        Route::get('/{employee}/edit', 'edit')->name('edit');
        Route::put('/{employee}', 'update')->name('update');
        Route::delete('/{employee}', 'destroy')->name('destroy');
        Route::get('/{employee}/photo', 'photo')->name('photo');
    });

    Route::controller(OrganizationalUnitController::class)->prefix('organizational-unit')->name('organizational-unit.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{organizational_unit}', 'show')->name('show');
        Route::get('/{organizational_unit}/edit', 'edit')->name('edit');
        Route::put('/{organizational_unit}', 'update')->name('update');
        Route::delete('/{organizational_unit}', 'destroy')->name('destroy');
    });

    Route::controller(PositionController::class)->prefix('position')->name('position.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{position}', 'show')->name('show');
        Route::get('/{position}/edit', 'edit')->name('edit');
        Route::put('/{position}', 'update')->name('update');
        Route::delete('/{position}', 'destroy')->name('destroy');
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
        Route::put('/{id}/roles', 'update_roles')->name('update-roles');
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
        Route::get('/{document}', 'show')->name('show');
        Route::put('/{document}', 'update')->name('update');
        Route::delete('/{document}', 'destroy')->name('destroy');
        Route::post('/{document}/copy', 'copy')->name('copy');
        Route::post('/{document}/finalize-satisfaction-notes', 'finalizeSatisfactionNotes')->name('finalize-satisfaction-notes');
        Route::post('/{document}/jobs', 'storeJob')->name('job.store');
        Route::put('/{document}/jobs/{job}', 'updateJob')->name('job.update');
        Route::delete('/{document}/jobs/{job}', 'destroyJob')->name('job.destroy');
        Route::post('/{document}/jobs/{job}/materials', 'storeMaterial')->name('material.store');
        Route::put('/{document}/jobs/{job}/materials/{material}', 'updateMaterial')->name('material.update');
        Route::delete('/{document}/jobs/{job}/materials/{material}', 'destroyMaterial')->name('material.destroy');
        Route::get('/{document}/jobs/{job}/photos', 'photos')->name('job.photos');
        Route::post('/{document}/jobs/{job}/photos', 'storePhoto')->name('job.photo.store');
        Route::delete('/{document}/jobs/{job}/photos/{photo}', 'destroyPhoto')->name('job.photo.destroy');
    });

    Route::get('/docking-space', [DockingManagementController::class, 'index_docking_space'])->name('docking-space.index');
    Route::post('/docking-space', [DockingManagementController::class, 'store_docking_space'])->name('docking-space.store');
    Route::get('/docking-space/{docking_space}/edit', [DockingManagementController::class, 'edit_docking_space'])->name('docking-space.edit');
    Route::put('/docking-space/{docking_space}', [DockingManagementController::class, 'update_docking_space'])->name('docking-space.update');
    Route::delete('/docking-space/{docking_space}', [DockingManagementController::class, 'destroy_docking_space'])->name('docking-space.destroy');

    Route::controller(ShipController::class)->prefix('ship-type')->name('ship-type.')->group(function (): void {
        Route::get('/', 'type_index')->name('index');
        Route::post('/', 'type_store')->name('store');
        Route::delete('/{id}', 'type_destroy')->name('destroy');
    });

    Route::controller(ShipController::class)->prefix('ship-classification')->name('ship-classification.')->group(function (): void {
        Route::get('/', 'class_index')->name('index');
        Route::post('/', 'class_store')->name('store');
        Route::delete('/{id}', 'class_destroy')->name('destroy');
    });

    Route::view('/price-dictionary', 'examples.placeholder', [
        'page_title' => 'Data Jenis Reparasi',
        'body_title' => 'Data Jenis Reparasi',
        'message' => 'Halaman data jenis reparasi belum diaktifkan.',
    ])->name('price-dictionary.index');

    Route::controller(DockingApprovalController::class)->prefix('docking-approval')->name('docking-approval.')->group(function (): void {
        Route::get('/{stage}', 'index')->whereIn('stage', ['engineering', 'production'])->name('index');
        Route::get('/{stage}/{docking_request}', 'show')->whereIn('stage', ['engineering', 'production'])->name('show');
    });

    Route::controller(DockingManagementController::class)->group(function (): void {
        Route::get('/docking-space-request/create', 'create_request')->name('docking-space-request.create');
        Route::post('/docking-space-request', 'store_request')->name('docking-space-request.store');
        Route::get('/docking-space-request', 'index_request')->name('docking-space-request.index');
        Route::post('/docking-space-request/{docking_request}/evaluate', 'evaluate_request')->name('docking-space-request.evaluate');
        Route::post('/docking-space-request/{docking_request}/engineering-review', 'engineering_review')->name('docking-space-request.engineering-review');
        Route::post('/docking-space-request/{docking_request}/production-review', 'production_review')->name('docking-space-request.production-review');
        Route::post('/docking-space-request/{docking_request}/cancel', 'cancel_request')->name('docking-space-request.cancel');
        Route::get('/docking-space-request/{docking_request}/documents/{document}', 'download_request_document')->name('docking-space-request.documents.download');
        Route::post('/docking-space-request/{docking_request}/start-docking', 'start_docking')->name('docking-space-request.start-docking');

        Route::get('/docking-space-availability', 'docking_space_availability')->name('docking-space-availability');

        Route::get('/ship-docking/current', 'current_docking')->name('ship-docking.index.current');
        Route::get('/ship-docking/history', 'docking_history')->name('ship-docking.history');
        Route::post('/ship-docking/occupancy/{occupancy}/undock-to-floating', 'undock_to_floating')->name('ship-docking.undock-to-floating');
        Route::post('/ship-docking/occupancy/{occupancy}/complete-floating', 'complete_floating')->name('ship-docking.complete-floating');
    });

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
