<?php

namespace App\Console\Commands;

use App\Services\IssueTypeService;
use App\Services\Jira\JiraApi;
use App\Services\ProjectService;
use Illuminate\Console\Command;
use App\Services\IssueService;

class SyncWithCloud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-with-cloud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /**
         * @var JiraApi $jiraApi
         */
        $jiraApi = app()->make(JiraApi::class);

        /**
         * @var IssueTypeService $typeService
         */
        $typeService = app()->make(IssueTypeService::class);

        /**
         * @var ProjectService $projectService
         */
        $projectService = app()->make(ProjectService::class);

        /**
         * @var IssueService $issueService
         */
        $issueService = app()->make(IssueService::class);

        // sync projects
        $projects = $jiraApi->getProjects();

        foreach ($projects as $project) {

            // store project
            if (!($localProject = $projectService->findByExternalId($project['id']))) {
                $localProject = $projectService->store([
                    'external_id' => $project['id'],
                    'key' => $project['key'],
                    'name' => $project['name'],
                ]);
            }

            // sync issue types
            $types = $jiraApi->getIssueTypes($project['id']);
            foreach ($types['projects'] as $type) {
                if (isset($type['issuetypes'])) {
                    foreach ($type['issuetypes'] as $typeDetail) {

                        if ($typeService->firstByExternalId($localProject->id, $typeDetail['id'])) {
                            continue;
                        }

                        $typeService->store([
                            'name' => $typeDetail['name'],
                            'external_id' => $typeDetail['id'],
                            'project_id' => $localProject->id
                        ]);
                    }
                }
            }

            // sync issues
            // get issues by chunks
            $counter = $startAt = 0;
            $perPage = 1;
            while ($issues = $jiraApi->getIssues($project['key'], $startAt, $perPage)) {

                foreach ($issues['issues'] as $issue) {

                    $existingIssue = $issueService->findByExternalId($issue['id']);

                    $desc = $jiraApi->extractDescription($issue['fields']['description'] ?? []);

                    $parentId = $this->getParentId($issue, $localProject);

                    if($existingIssue) {
                        $issueService->update($existingIssue->id, [
                            'summary' => $issue['fields']['summary'],
                            'description' => $desc,
                            'reporter_external_id' => $issue['fields']['reporter']['accountId'],
                            'parent_id'=> $parentId,
                        ]);
                        continue;
                    }

                    $desc = $jiraApi->extractDescription($issue['fields']['description'] ?? []);
                    $issueService->store([
                        'key' => $issue['key'],
                        'external_id' => $issue['id'],
                        'summary' => $issue['fields']['summary'],
                        'description' => $desc,
                        'project_id' => $localProject->id,
                        'type_id' => $typeService->firstByExternalId($localProject->id, $issue['fields']['issuetype']['id'])->id,
                        'reporter_external_id' => $issue['fields']['reporter']['accountId'],
                        'parent_id' => $parentId
                    ]);
                }

                if ($issues['maxResults'] + $issues['startAt'] > $issues['total']) {
                    break;
                }

                $counter++;
                $startAt = $counter * $perPage;

            }
        }
    }

    private function getParentId(array $issue, $localProject)
    {

        /**
         * @var IssueTypeService $typeService
         */
        $typeService = app()->make(IssueTypeService::class);
        /**
         * @var IssueService $issueService
         */
        $issueService = app()->make(IssueService::class);

        $parentId = null;
        if (isset($issue['fields']['parent']['id'])) {
            $parent = $issueService->findByExternalId($issue['fields']['parent']['id']);
            $parentIssue = $issue['fields']['parent'];
            $parentId = $parent->id ?? null;
            if (!$parent) {
                $parent = $issueService->store([
                    'key' => $parentIssue['key'],
                    'external_id' => $parentIssue['id'],
                    'summary' => $parentIssue['fields']['summary'],
                    'project_id' => $localProject->id,
                    'type_id' => $typeService->firstByExternalId($localProject->id, $issue['fields']['issuetype']['id'])->id,
                ]);
                $parentId = $parent->id;
            }
        }

        return $parentId;
    }
}
