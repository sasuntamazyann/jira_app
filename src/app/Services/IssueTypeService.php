<?php

namespace App\Services;

use App\Models\IssueType;

class IssueTypeService
{
    public function store(array $data)
    {
        return IssueType::create($data);
    }

    public function firstByExternalId($projectId, $externalId)
    {
        return IssueType::where('project_id', $projectId)->where('external_id', $externalId)->first();
    }

    public function findByName(string $name, $projectId)
    {
        return IssueType::where('project_id', $projectId)->where('name', $name)->first();
    }

    public function find($id)
    {
        return IssueType::find($id);
    }
}
