<?php

namespace App\Jobs;

use App\Services\IssueService;
use App\Services\IssueTypeService;
use App\Services\Jira\JiraApi;
use App\Services\ProjectService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JiraWebhookQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payload;
    /**
     * @var IssueService
     */
    private $issueService;

    /**
     * Create a new job instance.
     */
    public function __construct($webhookPayload)
    {
        $this->payload = $webhookPayload;
        $this->issueService = app()->make(IssueService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            switch ($this->payload['webhookEvent']) {
                case 'jira:issue_updated':
                    $items = $this->payload['changelog']['items'];

                    foreach ($items as $item) {
                        $updates = [];
                        if ($item['field'] == 'IssueParentAssociation') {
                            $updates = [
                                'parent_id' => $this->issueService->findByExternalId($item['toString'])->id
                            ];
                        } else {
                            $updates = [
                                $item['field'] => $item['toString']
                            ];
                        }

                        $this->issueService->updateByExternalId(
                            $this->payload['issue']['id'],
                            $updates
                        );
                    }

                    break;
                case 'jira:issue_created':
                    $issue = $this->payload['issue'];
                    $localProject = (new ProjectService())->findByExternalId(
                        $issue['fields']['project']['id']
                    );
                    $this->issueService->store(                    [
                        'key' => $issue['key'],
                        'external_id' => $issue['id'],
                        'summary' => $issue['fields']['summary'],
                        'description' => $issue['fields']['description'],
                        'project_id' => $localProject->id,
                        'type_id' => (new IssueTypeService())->firstByExternalId($localProject->id, $issue['fields']['issuetype']['id'])->id,
                        'reporter_external_id' => $issue['fields']['reporter']['accountId']
                    ]);

                    break;
                case 'jira:issue_deleted':
                    $issue = $this->payload['issue'];
                    $this->issueService->deleteByExternalId($issue['id']);
                    break;

            }


        } catch (\Exception $e) {
            \Log::error('errorik', [
                'm' => $e->getMessage(),
                't' => $e->getTrace(),
            ]);
        }
    }
}
