<?php

namespace App\Services;

use App\Models\Issue;

class IssueService
{
    public function store(array $data)
    {
        return Issue::create($data);
    }

    public function existsByExternalId($externalId)
    {
        return Issue::where('external_id', $externalId)->exists();
    }
}
