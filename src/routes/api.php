<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('projects', [ProjectController::class, 'index']);
Route::get('projects/{projectId}/issue-types', [ProjectController::class, 'getIssueTypes']);
Route::get('projects/{projectId}/issues', [IssueController::class, 'index']);
Route::post('projects/{projectId}/issues', [IssueController::class, 'store']);
Route::patch('projects/{projectId}/issues/{issueId}', [IssueController::class, 'update']);
Route::delete('projects/{projectId}/issues/{issueId}', [IssueController::class, 'delete']);

Route::any('webhooks', [WebhookController::class, 'handle']);

