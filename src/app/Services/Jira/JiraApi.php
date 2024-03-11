<?php

namespace App\Services\Jira;

use App\Services\IssueTypeService;
use App\Services\ProjectService;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Http;

class JiraApi
{
    protected $apis = [
        'getProjects' => 'rest/api/3/project',
        'getTypes' => 'rest/api/3/issue/createmeta',
        'getissues' => 'rest/api/3/search',
        'storeIssue' => 'rest/api/3/issue',
        'updateIssue' => 'rest/api/3/issue/',
        'deleteIssue' => 'rest/api/3/issue/',
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
                if(isset($content['content'])){
                    foreach ($content['content'] as $nestedContent) {
                        if (isset($nestedContent['text'])) {
                            $description .= $nestedContent['text'] ?? '';
                        }
                    }
                }
            }
        }

        return $description;
    }

    /**
     * @param $projectId
     * @param array $data
     * @return mixed
     */
    public function store($projectId, array $data)
    {

        $req = [
            'fields' => [
                'summary' => $data['summary'],
                'reporter' => [
                    'id' => config('services.jira.reportAccountId'),
                ],
                'issuetype' => [
                    'id' => (new  IssueTypeService)->find($data['type'])->external_id
                ],
                'project' => [
                    'key' => (new ProjectService())->find($projectId)->key
                ],

                'description' => [
                    'content' => [
                        [
                            'content' => [
                                [
                                    'text' => $data['description'],
                                    'type' => 'text'
                                ]
                            ],
                            'type' => 'paragraph',
                        ]
                    ],
                    "type" => "doc",
                    "version" => 1
                ]

            ]
        ];

        if (isset($data['parentKey'])) {
            $req['fields']['parent'] = [
                'key' => $data['parentKey']
            ];
        }

        return Http::baseUrl(config('services.jira.host'))
            ->withBasicAuth(config('services.jira.username'), config('services.jira.token'))
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->post($this->apis['storeIssue'],
                $req
            );
    }

    public function update($issueId, array $data)
    {
        return Http::baseUrl(config('services.jira.host'))
            ->withBasicAuth(config('services.jira.username'), config('services.jira.token'))
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->put($this->apis['updateIssue'] . $issueId,
                [
                'fields' => [
                    'summary' => $data['summary'],
                    'description' => [
                        'content' => [
                            [
                                'content' => [
                                    [
                                        'text' => $data['description'],
                                        'type' => 'text'
                                    ]
                                ],
                                'type' => 'paragraph',
                            ]
                        ],
                        "type" => "doc",
                        "version" => 1
                    ]
                ]
            ]
            );
    }

    public function delete($issueId)
    {
        return Http::baseUrl(config('services.jira.host'))
            ->withBasicAuth(config('services.jira.username'), config('services.jira.token'))
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->delete($this->apis['deleteIssue'] . $issueId);
    }
}
