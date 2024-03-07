<?php

namespace App\Http\Controllers;

use App\Http\Requests\Issue\CreateIssueRequest;
use App\Http\Requests\Issue\UpdateIssueRequest;
use App\Http\Resources\IssueResource;
use App\Services\IssueService;
use App\Services\Jira\JiraApi;
use App\Services\Jira\JiraService;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function index($projectId, IssueService $service, Request $request)
    {
        $i  = $service->paginate($projectId, $request->page, min($request->perPage, 100));

        return response()->json([
            'data' => IssueResource::collection($i->items()),
            'total' => $i->total(),
            'page' => $i->currentPage(),
            'perPage' => $i->perPage(),
        ]);
    }

    public function store($projectId, CreateIssueRequest $request, JiraApi $jiraApi, IssueService $service)
    {
        $v = $request->validated();

        $i  = $jiraApi->store($projectId, $v);

        $issue = $service->store([
            'key' => $i['key'],
            'external_id' => $i['id'],
            'summary' => $v['summary'],
            'description' => $v['description'],
            'project_id' => $projectId,
            'type_id' => $v['type'],
            'reporter_external_id' => config('services.jira.reportAccountId')
        ]);

        return response()->json([
            'data' => new IssueResource($issue),
        ]);
    }

    public function update($projectId, $issueId, UpdateIssueRequest $request, JiraApi $jiraApi, IssueService $service)
    {
        $v = $request->validated();

        $jiraApi->update(
            $service->find($issueId)->external_id,
            $v);

        $service->update($issueId, [
            'summary' => $v['summary'],
            'description' => $v['description'],
        ]);

        return response('', 204);
    }

    public function delete($projectId, $issueId, JiraApi $jiraApi, IssueService $service)
    {
        $jiraApi->delete(
                $service->find($issueId)->external_id,
            );
        $service->delete($issueId);

        return response('', 204);
    }
}
