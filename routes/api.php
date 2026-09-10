<?php

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
        Route::post('/agent/heartbeat', [\App\Http\Controllers\Api\V1\AgentApiController::class, 'heartbeat']);
        Route::post('/agent/metrics', [\App\Http\Controllers\Api\V1\AgentApiController::class, 'metrics']);
        Route::post('/agent/services', [\App\Http\Controllers\Api\V1\AgentApiController::class, 'services']);
        Route::post('/agent/events', [\App\Http\Controllers\Api\V1\AgentApiController::class, 'events']);
    });

    // Agent Registration (using static or UI-generated token, checked inside controller)
    Route::post('/agent/register', [\App\Http\Controllers\Api\V1\AgentApiController::class, 'register']);
});
