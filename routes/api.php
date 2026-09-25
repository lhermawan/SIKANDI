<?php

use App\Http\Controllers\Api\PublicIncidentReportApiController;
use App\Http\Controllers\Api\V1\AgentApiController;
use App\Http\Controllers\Api\V1\ApiController;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SIKANDI V1 REST API & Public Inbound
|--------------------------------------------------------------------------
*/

Route::post('/whatsapp/incident-reports', [PublicIncidentReportApiController::class, 'store'])->middleware('throttle:60,1');
Route::get('/whatsapp/incident-reports/{ticketNumber}', [PublicIncidentReportApiController::class, 'show'])->middleware('throttle:60,1');

Route::prefix('v1')->group(function () {
    // Auth Token
    Route::post('/auth/login', [ApiController::class, 'login'])->middleware('throttle:5,1');

    // User Authenticated Endpoints
    Route::middleware(['auth:sanctum', 'user.token'])->group(function () {
        // Assets (ITAM)
        Route::get('/assets', [ApiController::class, 'assets']);
        Route::get('/assets/{id}', [ApiController::class, 'assetDetail']);

        // CMDB
        Route::get('/ci', [ApiController::class, 'cis']);
        Route::get('/ci/{id}', [ApiController::class, 'ciDetail']);
        Route::get('/ci/{id}/relationships', [ApiController::class, 'ciRelationships']);

        // Service Desk
        Route::get('/tickets', [ApiController::class, 'tickets']);
        Route::post('/tickets', [ApiController::class, 'createTicket']);

        // Incidents
        Route::get('/incidents', [ApiController::class, 'incidents']);
        Route::post('/incidents', [ApiController::class, 'createIncident']);

        // Monitoring
        Route::get('/websites/status', [ApiController::class, 'websitesStatus']);

        // Assessment & IKASANDI
        Route::get('/assessments/summary', [ApiController::class, 'assessmentsSummary']);

        // CSIRT Security Incidents (Restricted to SOC Roles)
        Route::get('/security-incidents', [ApiController::class, 'securityIncidents'])->middleware('role:Super Admin|Admin Persandian');

        // Risk Register (Restricted to Risk Management Roles)
        Route::get('/risks', [ApiController::class, 'risks'])->middleware('role:Super Admin|Admin Persandian|Management');
    });

    // Agent Authenticated Endpoints
    Route::middleware(['auth:sanctum', 'agent.token'])->group(function () {
        Route::post('/agent/heartbeat', [AgentApiController::class, 'heartbeat']);
        Route::post('/agent/metrics', [AgentApiController::class, 'metrics']);
        Route::post('/agent/services', [AgentApiController::class, 'services']);
        Route::post('/agent/events', [AgentApiController::class, 'events']);
        Route::get('/agent/blacklist', [AgentApiController::class, 'blacklist']);
        Route::get('/agent/commands', [AgentApiController::class, 'fetchCommands']);
        Route::post('/agent/commands/{id}/result', [AgentApiController::class, 'submitCommandResult']);
    });

    // Agent Registration (Rate limited to prevent brute force)
    Route::post('/agent/register', [AgentApiController::class, 'register'])->middleware('throttle:10,1');
});
