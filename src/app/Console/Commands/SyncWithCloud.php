<?php

namespace App\Console\Commands;

use App\Services\IssueTypeService;
use App\Services\Jira\JiraApi;
use App\Services\Jira\JiraService;
use App\Services\ProjectService;
use DH\Adf\Node\Block\Document;
use Illuminate\Console\Command;
use JiraCloud\ADF\AtlassianDocumentFormat;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueType;
use JiraCloud\JiraException;
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

                    if ($issueService->existsByExternalId($issue['id'])) {
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
                        'reporter_external_id' => $issue['fields']['reporter']['accountId']
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
}
