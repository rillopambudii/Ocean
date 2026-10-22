<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\MasterLocationController;
use App\Http\Controllers\ManHourController;
use App\Http\Controllers\HsseStatisticController;
use App\Http\Controllers\IncidentResumeController;
use App\Http\Controllers\AnnualWorkPlanController;
use App\Http\Controllers\ManagementReviewController;
use App\Http\Controllers\ManagementVisitController;
use App\Http\Controllers\InternalAuditController;
use App\Http\Controllers\AuditFindingController;
use App\Http\Controllers\AuditChecklistTemplateController;
use App\Http\Controllers\VesselCertificateController;
use App\Http\Controllers\MaintenanceChecklistController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CrewController;
use App\Http\Controllers\VesselSurveyController;
use App\Http\Controllers\DamageReportController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\BudgetController;

Route::get('/', function () {
    return redirect()->route('landing');
});

Route::get('/public-dashboard', [PublicDashboardController::class, 'index']);

// Landing page publik (hero parallax + chart live) — design-system/pages/landing.md
Route::get('/landing', [PublicDashboardController::class, 'landing'])->name('landing');

// Living style guide — preview design system dari design-system/MASTER.md
Route::view('/design-system', 'design-system')->name('design-system');

Route::middleware(['auth'])->group(function () {

    Route::get(
        '/admin/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | DOKUMEN (QHSE & DEPARTEMEN) — hak akses dicek per kategori di controller
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/documents/{category}')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::get('/create', [DocumentController::class, 'create']);
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('/{id}/edit', [DocumentController::class, 'edit'])->whereNumber('id');
        Route::put('/{id}', [DocumentController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [DocumentController::class, 'destroy'])->whereNumber('id');
        Route::get('/{id}/file', [DocumentController::class, 'download'])->whereNumber('id');
    });

    
    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::resource('admin/users', UserManagementController::class);
        Route::resource('admin/master-location', MasterLocationController::class);

        // Departemen Technical
        Route::resource('admin/vessel-surveys', VesselSurveyController::class)->except('show');
        Route::resource('admin/damage-reports', DamageReportController::class)->except('show');

        // Departemen Purchasing
        Route::resource('admin/purchase-requests', PurchaseRequestController::class);
        Route::get('admin/purchase-requests/{purchaseRequest}/attachment', [PurchaseRequestController::class, 'attachment']);
        Route::post('admin/purchase-requests/{purchaseRequest}/items', [PurchaseRequestController::class, 'storeItem']);
        Route::delete('admin/purchase-request-items/{item}', [PurchaseRequestController::class, 'destroyItem']);

        // Departemen Budget Plan & Monitoring
        Route::get('admin/budgets', [BudgetController::class, 'index']);
        Route::get('admin/budgets/plan', [BudgetController::class, 'plan']);
        Route::post('admin/budgets/plan', [BudgetController::class, 'savePlan']);

        // Departemen Crewing
        Route::resource('admin/crews', CrewController::class);
        Route::post('admin/crews/{crew}/certificates', [CrewController::class, 'storeCertificate']);
        Route::delete('admin/crew-certificates/{certificate}', [CrewController::class, 'destroyCertificate']);
    });

    /*
    |--------------------------------------------------------------------------
    | QHSE
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Admin QHSE'])->group(function () {
        Route::resource('admin/man-hours', ManHourController::class);
        Route::resource('admin/hsse-statistics', HsseStatisticController::class);
        Route::resource('admin/incident-resume', IncidentResumeController::class);
        Route::resource('admin/annual-work-plans', AnnualWorkPlanController::class);
        Route::resource('admin/management-reviews', ManagementReviewController::class);
        Route::resource('admin/management-visits', ManagementVisitController::class);
        Route::resource('admin/internal-audits', InternalAuditController::class);
        Route::resource('admin/audit-findings', AuditFindingController::class);
        Route::resource('admin/audit-checklist-templates', AuditChecklistTemplateController::class);

        Route::get(
            'admin/internal-audits/{id}/checklist',
            [InternalAuditController::class, 'checklist']
        );

        Route::post(
            'admin/internal-audits/{id}/checklist',
            [InternalAuditController::class, 'storeChecklist']
        );

        Route::get(
            'admin/internal-audits/{id}/export-pdf',
            [InternalAuditController::class, 'exportPdf']
        );

        Route::get(
            'admin/annual-work-plans/{id}/schedules',
            [AnnualWorkPlanController::class, 'schedules']
        );

        Route::post(
            'admin/annual-work-plans/{id}/schedules',
            [AnnualWorkPlanController::class, 'storeSchedule']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | OPERATION
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Admin Operation'])->group(function () {
        Route::resource('admin/vessel-certificates', VesselCertificateController::class);
        Route::resource('admin/maintenance-checklists', MaintenanceChecklistController::class);

        Route::get(
            'admin/maintenance-checklists/{id}/items',
            [MaintenanceChecklistController::class, 'items']
        );

        Route::post(
            'admin/maintenance-checklists/{id}/items',
            [MaintenanceChecklistController::class, 'storeItem']
        );

        Route::post(
            'admin/maintenance-items/{id}/daily-checks',
            [MaintenanceChecklistController::class, 'storeDailyCheck']
        );

        Route::get(
            'admin/vessel-certificates-export',
            [VesselCertificateController::class, 'exportExcel']
        );
    });
});

Route::get('/force-logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login');
    });


require __DIR__.'/auth.php';