<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Services\Jira\JiraService;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(ProjectService $service)
    {
        return response()->json([
            'data' => ProjectResource::collection(
                $service->all()
            )
        ]);
    }
}
