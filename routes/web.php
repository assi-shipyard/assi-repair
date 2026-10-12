<?php

use App\Http\Controllers\account_management_controller;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\customer_portal_controller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DockingApprovalController;
use App\Http\Controllers\DockingDashboardController;
use App\Http\Controllers\DockingManagementController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotificationFlagController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\OrganizationalUnitController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\PriceBookController;
use App\Http\Controllers\project_boq_controller;
use App\Http\Controllers\project_job_group_controller;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectJobDocumentPageController;
use App\Http\Controllers\RepairTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShipController;
use App\Http\Controllers\ShipDocumentController;
use App\Http\Controllers\subcontractor_controller;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'login_process'])->name('login.process');
    Route::get('/admin/login', [LoginController::class, 'admin_login'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'admin_login_process'])->name('admin.login.process');
    Route::get('/customer/login', [customer_portal_controller::class, 'login'])->name('customer.login');
    Route::post('/customer/login', [customer_portal_controller::class, 'login_process'])->middleware('throttle:6,1')->name('customer.login.process');
});

Route::get('/logout', [LoginController::class, 'logout'])->middleware(['auth', 'account_access'])->name('logout');

Route::middleware(['auth', 'account_access'])->prefix('customer')->name('customer.')->group(function (): void {
    Route::get('/', [customer_portal_controller::class, 'index'])->name('index');
    Route::get('/settings', [customer_portal_controller::class, 'settings'])->name('settings');
    Route::put('/password', [customer_portal_controller::class, 'password'])->middleware('throttle:6,1')->name('password');
    Route::get('/project/{project_id}', [customer_portal_controller::class, 'project'])->whereUuid('project_id')->name('project');
    Route::post('/project/{project_id}/feedback', [customer_portal_controller::class, 'feedback'])->whereUuid('project_id')->middleware('throttle:10,1')->name('feedback');
    Route::get('/document/{document}', [customer_portal_controller::class, 'document'])->whereUuid('document')->name('document');
    Route::get('/document/{document}/data', [customer_portal_controller::class, 'document_data'])->whereUuid('document')->name('document.data');
    Route::post('/document/{document}/unlock', [customer_portal_controller::class, 'unlock'])->whereUuid('document')->middleware('throttle:6,1')->name('unlock');
    Route::post('/document/{document}/lock', [customer_portal_controller::class, 'lock'])->whereUuid('document')->name('lock');
    Route::post('/document/{document}/decision', [customer_portal_controller::class, 'decide'])->whereUuid('document')->middleware('throttle:10,1')->name('decision');
});

Route::middleware(['auth', 'account_access', 'audit_log'])->group(function (): void {
    Route::middleware('role:admin')->group(function (): void {
        Route::get('/accounts', [account_management_controller::class, 'index'])->name('accounts.index');
        Route::post('/accounts', [account_management_controller::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{user}/edit', [account_management_controller::class, 'edit'])->whereUuid('user')->name('accounts.edit');
        Route::put('/accounts/{user}', [account_management_controller::class, 'update'])->whereUuid('user')->name('accounts.update');
        Route::put('/accounts/{user}/status', [account_management_controller::class, 'status'])->whereUuid('user')->name('accounts.status');
        Route::post('/customer-management/access', [account_management_controller::class, 'access'])->name('accounts.access');
    });
    Route::get('/customer-management', [account_management_controller::class, 'projects'])->name('accounts.projects');
    Route::post('/customer-management/publish', [account_management_controller::class, 'publish'])->name('accounts.publish');
    Route::put('/customer-management/document/{document}/approver', [account_management_controller::class, 'reassign'])->whereUuid('document')->name('accounts.reassign');
    Route::put('/customer-management/feedback/{feedback}', [account_management_controller::class, 'respond'])->whereUuid('feedback')->name('accounts.respond');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/docking-dashboard', [DockingDashboardController::class, 'index'])->name('docking-dashboard');
    Route::get('/subcontractor', [subcontractor_controller::class, 'index'])->name('subcontractor.index');
    Route::middleware('role:admin')->group(function (): void {
        Route::get('/subcontractor/create', [subcontractor_controller::class, 'create'])->name('subcontractor.create');
        Route::post('/subcontractor', [subcontractor_controller::class, 'store'])->name('subcontractor.store');
        Route::get('/subcontractor/{subcontractor}/edit', [subcontractor_controller::class, 'edit'])->whereUuid('subcontractor')->name('subcontractor.edit');
        Route::put('/subcontractor/{subcontractor}', [subcontractor_controller::class, 'update'])->whereUuid('subcontractor')->name('subcontractor.update');
    });

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
        Route::get('/{company}/logo', 'logo')->name('logo');
        Route::post('/{company}/logo', 'upload_logo')->name('upload-logo');
        Route::post('/{company}/documents', 'upload_document')->name('upload-document');
        Route::delete('/{company}/documents/{document}', 'delete_document')->name('delete-document');
        Route::get('/{company}/documents/{document}/download', 'download_document')->name('documents.download');
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
        Route::post('/{ship}/photos', 'store_photo')->name('photos.store');
        Route::get('/{ship}/photos/{photo}', 'photo')->name('photos.show');
        Route::delete('/{ship}/photos/{photo}', 'destroy_photo')->name('photos.destroy');

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

    Route::resource('/notification-flag', NotificationFlagController::class)->except(['show']);

    Route::controller(NotificationSettingController::class)->prefix('notification-settings')->name('notification-settings.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');
    });

    Route::controller(ProjectController::class)->prefix('project')->name('project.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/history', 'history')->name('history');
        Route::get('/code-preview', 'generate_code_preview')->name('generate-code-preview');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::controller(project_boq_controller::class)->prefix('project/{projectId}/job-document')->name('project.job-document.workflow.')
        ->whereUuid(['projectId', 'document', 'job', 'material'])->group(function (): void {
            Route::get('/price-items', 'price_items')->name('price-items');
            Route::get('/{document}/export', 'export')->name('export');
            Route::post('/{document}/jobs', 'store_job')->name('job.store');
            Route::put('/{document}/jobs/{job}', 'update_job')->name('job.update');
            Route::delete('/{document}/jobs/{job}', 'destroy_job')->name('job.destroy');
            Route::post('/{document}/jobs/{job}/materials', 'store_material')->name('material.store');
            Route::put('/{document}/jobs/{job}/materials/{material}', 'update_material')->name('material.update');
            Route::delete('/{document}/jobs/{job}/materials/{material}', 'destroy_material')->name('material.destroy');
        });

    Route::controller(ProjectJobDocumentPageController::class)->prefix('project/{projectId}/job-document')->name('project.job-document.workflow.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{document}', 'show')->name('show');
        Route::get('/{document}/compare', 'compare')->name('compare');
        Route::post('/{document}/submit', 'submit')->name('submit');
        Route::post('/{document}/approve', 'approve')->name('approve');
        Route::post('/{document}/return-to-draft', 'return_to_draft')->name('return-to-draft');
        Route::post('/{document}/finalize-satisfaction-notes', 'finalizeSatisfactionNotes')->name('finalize-satisfaction-notes');
        Route::get('/{document}/jobs/{job}/photos', 'photos')->name('job.photos');
        Route::post('/{document}/jobs/{job}/photos', 'storePhoto')->name('job.photo.store');
        Route::get('/{document}/jobs/{job}/photos/{photo}/file', 'photo')->name('job.photo.show');
        Route::delete('/{document}/jobs/{job}/photos/{photo}', 'destroyPhoto')->name('job.photo.destroy');
    });

    Route::controller(project_job_group_controller::class)->prefix('job-group')->name('job-group.')->middleware('role:admin')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{job_group}', 'update')->name('update');
        Route::delete('/{job_group}', 'destroy')->name('destroy');
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

    Route::controller(RepairTypeController::class)->prefix('price-dictionary')->name('price-dictionary.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{repair_type}', 'update')->name('update');
        Route::delete('/{repair_type}', 'destroy')->name('destroy');
    });

    Route::controller(PriceBookController::class)->prefix('price-book')->name('price-book.')->scopeBindings()->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::middleware('role:admin')->group(function (): void {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{price_book}/edit', 'edit')->name('edit');
            Route::put('/{price_book}', 'update')->name('update');
            Route::delete('/{price_book}', 'destroy')->name('destroy');
            Route::get('/{price_book}/items/create', 'create_item')->name('items.create');
            Route::post('/{price_book}/items', 'store_item')->name('items.store');
            Route::get('/{price_book}/items/{item}/edit', 'edit_item')->name('items.edit');
            Route::put('/{price_book}/items/{item}', 'update_item')->name('items.update');
            Route::delete('/{price_book}/items/{item}', 'destroy_item')->name('items.destroy');
        });
        Route::get('/{price_book}', 'show')->name('show');
    });

    Route::controller(DockingApprovalController::class)->prefix('docking-approval')->name('docking-approval.')->group(function (): void {
        Route::get('/{stage}', 'index')->whereIn('stage', ['engineering', 'production'])->name('index');
        Route::get('/{stage}/{docking_request}', 'show')->whereIn('stage', ['engineering', 'production'])->name('show');
    });

    Route::controller(DockingManagementController::class)->group(function (): void {
        Route::get('/docking-space-request/create', 'create_request')->name('docking-space-request.create');
        Route::post('/docking-space-request', 'store_request')->name('docking-space-request.store');
        Route::get('/docking-space-request', 'index_request')->name('docking-space-request.index');
        Route::get('/docking-space-request/rejected', 'rejected_requests')->name('docking-space-request.rejected');
        Route::post('/docking-space-request/{docking_request}/evaluate', 'evaluate_request')->name('docking-space-request.evaluate');
        Route::post('/docking-space-request/{docking_request}/engineering-review', 'engineering_review')->name('docking-space-request.engineering-review');
        Route::post('/docking-space-request/{docking_request}/production-review', 'production_review')->name('docking-space-request.production-review');
        Route::post('/docking-space-request/{docking_request}/marketing-response', 'respond_to_recommendation')->name('docking-space-request.marketing-response');
        Route::get('/docking-space-request/{docking_request}/postpone', 'postpone_form')->name('docking-space-request.postpone');
        Route::post('/docking-space-request/{docking_request}/postpone', 'postpone_docking')->name('docking-space-request.postpone.store');
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
