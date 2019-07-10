<?php

namespace Kaweb\Jira;

/**
 * Used to interact with a Jira instance.
 *
 * @package Kaweb\Jira
 * @see     https://docs.atlassian.com/jira/REST/latest/
 * @since   1.0
 */
class Jira extends AtlassianConnection
{
    /**
     * Statically get an instance of this class
     *
     * @return Kaweb\Jira
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Create an issue in JIRA.
     *
     * @param  string $summary     Summary text.
     * @param  string $description Description text.
     * @param  string $project     A project key, defaults to 'TP' for test project.
     * @param  string $priority    (Normal (default), High, Critical, Wishlist)
     * @param  string $type        Issue type (Alert (default), Bug, etc.)
     * @param  string $assignee    User to which issue is to be assigned.
     *
     * @return object
     */
    public function createIssue(
        string $summary,
        string $description,
        string $project,
        string $priority,
        string $type,
        string $assignee
    ): object {
        if (isset($_SERVER['argv'])) {
            $description .= "\n\nArguments:\n\n";
            foreach ($_SERVER['argv'] as $i => $arg) {
                $description .= "# {$arg}\n";
            }
        }

        if (!in_array($priority, ['Blocker', 'Critical', 'Major', 'Minor', 'Trivial'])) {
            $priority = 'Normal';
        }

        $data = [
            'fields' => [
                'project' => [
                    'key' => $project,
                ],
                'summary' => $summary,
                'description' => $description,
                'issuetype' => [
                    'name' => $type,
                    'subtask' => false
                ],
                'priority' => [
                    'name' => $priority
                ]
            ],
        ];

        if (!is_null($assignee)) {
            $data['fields']['assignee'] = ['name' => $assignee];
        }

        return $this->post('/rest/api/2/issue/', $data);
    }

    /**
     * Get an existing open issue by issue key
     *
     * @param string $issueKey
     *
     * @return string|null issue key
     */
    public function getIssue($issueKey):? string
    {
        return $this->get('/rest/api/2/issue/' . $issueKey);
    }

    /**
     * Queries Jira using JQL markup
     *
     * @param  string $jql
     *
     * @return string
     */
    public function findByJQL($jql): string
    {
        $data = [
            'jql' => $jql,
        ];

        $responseObject = $this->post('/rest/api/2/search', $data);

        if (empty($responseObject->total)) {
            return null;
        }

        return $responseObject;
    }

    /**
     * Find an existing open issue by summary
     *
     * @param string $summary
     * @param string $project
     * @param boolean $openOnly
     *
     * @return string|null issue key
     */
    public function findIssueBySummary($summary, $project, $openOnly = false):? string
    {
        $escapedSummary = preg_replace('/[^A-Za-z0-9]+/', ' ', $summary);

        $jql = "summary ~ \"$escapedSummary\" AND project = {$project}";
        if ($openOnly) {
            $jql .= " AND status = Open";
        }

        $data = [
            'jql' => $jql,
            'maxResults' => 1,
            'fields' => ['key']
        ];

        $responseObject = $this->post('/rest/api/2/search', $data);

        if (empty($responseObject->total)) {
            return null;
        }

        return $responseObject->issues[0]->key;
    }

    /**
     * Add a comment to an issue
     *
     * @param  string $issueKey
     * @param  string $comment
     * @param  string $restrictionLevel
     *
     * @return object
     */
    public function addComment(string $issueKey, string $comment, string $restrictionLevel = null): object
    {
        $data = [
            'body' => $comment
        ];

        if ($restrictionLevel) {
            $data['visibility'] = [
                'type' => 'role',
                'value' => $restrictionLevel
            ];
        }

        return $this->post("/rest/api/2/issue/{$issueKey}/comment", $data);
    }
}
