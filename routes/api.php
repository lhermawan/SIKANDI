<?php

use App\Http\Controllers\Api\V1\AgentApiController;
use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SIKANDI V1 REST API
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Auth Token
    Route::post('/auth/login', [ApiController::class, 'login']);

    // Public / Read-only or Sanctum Authenticated
    Route::middleware('auth:sanctum')->group(function () {
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

        // CSIRT Security Incidents
        Route::get('/security-incidents', [ApiController::class, 'securityIncidents']);

        // Assessment & IKASANDI
        Route::get('/assessments/summary', [ApiController::class, 'assessmentsSummary']);

        // Risk Register
        Route::get('/risks', [ApiController::class, 'risks']);

        // Agent Communication
        Route::post('/agent/heartbeat', [AgentApiController::class, 'heartbeat']);
        Route::post('/agent/metrics', [AgentApiController::class, 'metrics']);
        Route::post('/agent/services', [AgentApiController::class, 'services']);
        Route::post('/agent/events', [AgentApiController::class, 'events']);
        Route::get('/agent/blacklist', [AgentApiController::class, 'blacklist']);
        Route::get('/agent/commands', [AgentApiController::class, 'fetchCommands']);
        Route::post('/agent/commands/{id}/result', [AgentApiController::class, 'submitCommandResult']);
    });

    // Agent Registration (using static or UI-generated token, checked inside controller)
    Route::post('/agent/register', [AgentApiController::class, 'register']);
});
