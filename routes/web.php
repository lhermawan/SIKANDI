<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CmdbController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiskManagerController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\SecurityIncidentController;
use App\Http\Controllers\SecurityLogController;
use App\Http\Controllers\SecurityRuleController;
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
    Route::post('/monitoring/websites', [MonitoringController::class, 'storeWebsite'])->name('monitoring.websites.store');
    Route::post('/monitoring/websites/{website}/check', [MonitoringController::class, 'check'])->name('monitoring.websites.check');

    // Incident Management
    Route::resource('incidents', IncidentController::class)->except(['destroy']);
    Route::delete('incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');
    Route::post('/incidents/{incident}/comment', [IncidentController::class, 'addComment'])->name('incidents.comment');

    // Security, CSIRT Incidents, and Agents
    Route::middleware(['role:Super Admin|Admin Persandian'])->group(function () {
        Route::prefix('security')->name('security.')->group(function () {
            Route::get('/logs', [SecurityLogController::class, 'index'])->name('logs.index');
            Route::get('/logs/{event}', [SecurityLogController::class, 'show'])->name('logs.show');

            // Existing Incident routes
            Route::get('/incidents', [SecurityIncidentController::class, 'index'])->name('incidents.index');
            Route::get('/incidents/create', [SecurityIncidentController::class, 'create'])->name('incidents.create');
            Route::post('/incidents', [SecurityIncidentController::class, 'store'])->name('incidents.store');
            Route::get('/incidents/{incident}', [SecurityIncidentController::class, 'show'])->name('incidents.show');
            Route::post('/incidents/{incident}/workflow', [SecurityIncidentController::class, 'updateWorkflow'])->name('incidents.workflow');
            Route::post('/incidents/{incident}/tasks', [SecurityIncidentController::class, 'storeTask'])->name('incidents.tasks.store');
            Route::post('/incidents/{incident}/tasks/{task}/toggle', [SecurityIncidentController::class, 'toggleTask'])->name('incidents.tasks.toggle');
            Route::post('/incidents/{incident}/responses', [SecurityIncidentController::class, 'storeResponse'])->name('incidents.responses.store');
            Route::post('/incidents/responses/{response}/execute', [SecurityIncidentController::class, 'executeAction'])->name('incidents.responses.execute');
            Route::post('/incidents/responses/bulk-execute', [SecurityIncidentController::class, 'executeBulkAction'])->name('incidents.responses.bulk-execute');
            Route::post('/incidents/{incident}/evidence', [SecurityIncidentController::class, 'storeEvidence'])->name('incidents.evidence.store');
            Route::post('/incidents/{incident}/resolve', [SecurityIncidentController::class, 'resolve'])->name('incidents.resolve');
            Route::post('/incidents/{incident}/assign', [SecurityIncidentController::class, 'assign'])->name('incidents.assign');
            Route::delete('/incidents/{incident}', [SecurityIncidentController::class, 'destroy'])->name('incidents.destroy');

            // Threat Actors & HitL Approvals
            Route::get('/threat-actors', [DashboardController::class, 'threatActors'])->name('threat-actors.index');
            Route::post('/threat-actors/bulk-block', [DashboardController::class, 'draftQuickBlockBulk'])->name('threat-actors.bulk-block');
            Route::post('/threat-actors/whitelist', [DashboardController::class, 'whitelistIp'])->name('threat-actors.whitelist');
            Route::post('/threat-actors/unban', [DashboardController::class, 'unbanIp'])->name('threat-actors.unban');
            Route::get('/approvals', [DashboardController::class, 'socApprovals'])->name('approvals.index');

            // Rules
            Route::get('/rules', [SecurityRuleController::class, 'index'])->name('rules.index');
            Route::post('/rules', [SecurityRuleController::class, 'store'])->name('rules.store');
            Route::put('/rules/{rule}', [SecurityRuleController::class, 'update'])->name('rules.update');
            Route::post('/rules/{rule}/toggle', [SecurityRuleController::class, 'toggle'])->name('rules.toggle');

            Route::get('/risks', [RiskController::class, 'index'])->name('risks.index');
            Route::get('/risks/create', [RiskController::class, 'create'])->name('risks.create');
            Route::post('/risks', [RiskController::class, 'store'])->name('risks.store');
            Route::get('/risks/{risk}', [RiskController::class, 'show'])->name('risks.show');
            Route::post('/risks/{risk}/treatment', [RiskController::class, 'storeTreatment'])->name('risks.treatment');
        });

        // Agents
        Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
        Route::get('/agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
        Route::post('/agents/{agent}/approve', [AgentController::class, 'approve'])->name('agents.approve');
        Route::post('/agents/{agent}/revoke', [AgentController::class, 'revoke'])->name('agents.revoke');
        Route::post('/agents/{agent}/link', [AgentController::class, 'link'])->name('agents.link');
        Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
        Route::post('/agents/registration-token', [AgentController::class, 'generateRegistrationToken'])->name('agents.token');

        // Agent Disk Manager
        Route::get('/agents/{agent}/disk', [DiskManagerController::class, 'show'])->name('agents.disk.show');
        Route::post('/agents/{agent}/disk/scan', [DiskManagerController::class, 'requestScan'])->name('agents.disk.scan');
        Route::post('/agents/{agent}/disk/delete', [DiskManagerController::class, 'requestDelete'])->name('agents.disk.delete');
    });
});
