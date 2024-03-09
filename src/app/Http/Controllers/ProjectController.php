<?php

namespace App\Http\Controllers;

use App\Http\Resources\IssueTypeResource;
use App\Http\Resources\ProjectResource;
use App\Services\IssueTypeService;
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

    public function getIssueTypes($projectId, IssueTypeService $service)
    {
        return response()->json([
            'data' => IssueTypeResource::collection(
                $service->getByProject($projectId)
            )
        ]);
    }
}
