<?php

namespace App\Services;

use App\Models\Project;

class ProjectService
{

    /**
     * @param array $data
     * @return Project
     */
    public function store(array $data)
    {
        return Project::create($data);
    }

    public function existsByExternalId(string $externalId)
    {
        return Project::where('external_id', $externalId)->exists();
    }

    public function findByExternalId($externalId)
    {
        return Project::where('external_id', $externalId)->first();
    }

    public function find($id)
    {
        return Project::find($id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Project::all();
    }
}
