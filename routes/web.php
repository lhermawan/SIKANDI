<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CmdbController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\IkasandiController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\SecurityIncidentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIKANDI Diskominfo Kabupaten Ciamis
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Hardware Label Public Scan (Mobile QR Scan)
Route::get('/itam/scan/{token}', [AssetController::class, 'scan'])->name('itam.scan');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Default Route
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard Hub
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search (Live Spotlight)
    Route::get('/global-search', [GlobalSearchController::class, 'search'])->name('global.search');

    // CMDB (Configuration Management Database)
    Route::get('/cmdb/graph', [CmdbController::class, 'graph'])->name('cmdb.graph');
    Route::get('/cmdb/graph/data', [CmdbController::class, 'graphData'])->name('cmdb.graph.data');
    Route::get('/cmdb/relationships', [CmdbController::class, 'relationships'])->name('cmdb.relationships');
    Route::post('/cmdb/relationships', [CmdbController::class, 'storeRelationship'])->name('cmdb.relationships.store');
    Route::delete('/cmdb/relationships/{relationship}', [CmdbController::class, 'destroyRelationship'])->name('cmdb.relationships.destroy');
    Route::resource('cmdb', CmdbController::class)->parameters(['cmdb' => 'cmdb']);

    // IT Asset Management (ITAM)
    Route::get('/itam/{asset}/print-label', [AssetController::class, 'printLabel'])->name('itam.print-label');
    Route::resource('itam', AssetController::class)->parameters(['itam' => 'asset']);

    // IT Service Desk (Tiket Layanan)
    Route::prefix('service-desk')->name('service-desk.')->group(function () {
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets');
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/comment', [TicketController::class, 'addComment'])->name('tickets.comment');
        Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    });

    // Website & SSL Monitoring
    Route::get('/monitoring/websites', [MonitoringController::class, 'websites'])->name('monitoring.websites');
    Route::post('/monitoring/websites/{website}/check', [MonitoringController::class, 'check'])->name('monitoring.websites.check');

    // Incident Management
    Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
    Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
    Route::get('/incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
    Route::put('/incidents/{incident}', [IncidentController::class, 'update'])->name('incidents.update');
    Route::post('/incidents/{incident}/comment', [IncidentController::class, 'addComment'])->name('incidents.comment');

    // Security & CSIRT Incidents
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/incidents', [SecurityIncidentController::class, 'index'])->name('incidents');
        Route::get('/incidents/create', [SecurityIncidentController::class, 'create'])->name('incidents.create');
        Route::post('/incidents', [SecurityIncidentController::class, 'store'])->name('incidents.store');
        Route::post('/incidents/{incident}/workflow', [SecurityIncidentController::class, 'updateWorkflow'])->name('incidents.workflow');

        // Risk Management & Matrix
        Route::get('/risks', [RiskController::class, 'index'])->name('risks');
        Route::get('/risks/create', [RiskController::class, 'create'])->name('risks.create');
        Route::post('/risks', [RiskController::class, 'store'])->name('risks.store');
        Route::post('/risks/{risk}/treatment', [RiskController::class, 'storeTreatment'])->name('risks.treatment');
    });

    // IKASANDI (Indikator Keamanan Informasi OPD)
    Route::prefix('ikasandi')->name('ikasandi.')->group(function () {
        Route::get('/dashboard', [IkasandiController::class, 'dashboard'])->name('dashboard');
        Route::get('/assessment', [IkasandiController::class, 'assessment'])->name('assessment');
        Route::post('/assessment/{assessment}', [IkasandiController::class, 'submitAssessment'])->name('assessment.submit');
    });

    // Knowledge Base & Documentation
    Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');

    // Administration (Roles & OPD)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
    });

    // Agents
    Route::get('/agents', [\App\Http\Controllers\AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/{agent}', [\App\Http\Controllers\AgentController::class, 'show'])->name('agents.show');
    Route::post('/agents/{agent}/approve', [\App\Http\Controllers\AgentController::class, 'approve'])->name('agents.approve');
    Route::post('/agents/{agent}/revoke', [\App\Http\Controllers\AgentController::class, 'revoke'])->name('agents.revoke');
    Route::post('/agents/{agent}/link', [\App\Http\Controllers\AgentController::class, 'link'])->name('agents.link');
    Route::post('/agents/registration-token', [\App\Http\Controllers\AgentController::class, 'generateRegistrationToken'])->name('agents.token');
});
