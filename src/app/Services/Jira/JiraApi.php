<?php

namespace App\Services\Jira;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Http;

class JiraApi
{
    protected $apis = [
        'getProjects' => 'rest/api/3/project',
        'getTypes' => 'rest/api/3/issue/createmeta',
        'getissues' => 'rest/api/3/search',
    ];


    /**
     * @return string
     */
    public function getToken()
    {
        return base64_encode(
            config('services.jira.username') . ':' . config('services.jira.token')
        );
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return config('services.jira.host');
    }

    public function getProjects()
    {
        return Http::baseUrl(config('services.jira.host'))->withBasicAuth(config('services.jira.username'), config('services.jira.token'))->get($this->apis['getProjects'])->json();
    }

    public function getIssueTypes($projectId)
    {
        return Http::baseUrl(config('services.jira.host'))
            ->withBasicAuth(config('services.jira.username'), config('services.jira.token'))
            ->get($this->apis['getTypes'], [
                'expand' => 'projects.issuetypes.fields',
                'projectIds' => $projectId
            ])
            ->json();
    }

    public function getIssues($projectKey, int $startAt = 0, int $maxResult)
    {
        return Http::baseUrl(config('services.jira.host'))
            ->withBasicAuth(config('services.jira.username'), config('services.jira.token'))
            ->get($this->apis['getissues'], [
                'jql' => 'project=' . $projectKey,
                'startAt' => $startAt,
                'maxResults' => $maxResult
            ])
            ->json();
    }

    public function extractDescription(array $desc)
    {
        $description = '';
        if (isset($desc['content'])) {
            foreach ($desc['content'] as $content) {
                if (isset($content['type']) && $content['type'] === 'paragraph') {
                    $description .= $content['content']['text'] ?? '';
                }
            }
        }

        return $description;
    }
}
