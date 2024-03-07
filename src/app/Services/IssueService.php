<?php

namespace App\Services;

use App\Models\Issue;

class IssueService
{
    public function store(array $data)
    {
        return Issue::create($data);
    }

    public function update($id, array $data)
    {
        return Issue::where('id', $id)->update($data);
    }

    public function existsByExternalId($externalId)
    {
        return Issue::where('external_id', $externalId)->exists();
    }

    public function find($id)
    {
        return Issue::find($id);
    }

    /**
     * @param $projectId
     * @param $page
     * @param $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($projectId, $page, $perPage)
    {
        return Issue::query()->with('type')->where('project_id', $projectId)->paginate(page: $page, perPage: $perPage);
    }

    public function delete($id)
    {
        return Issue::where('id', $id)->delete();
    }
}
