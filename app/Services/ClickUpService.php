<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickUpService
{
    private string $baseUrl = 'https://api.clickup.com/api/v2';
    private string $token;

    public function __construct()
    {
        $this->token = config('services.clickup.token');
    }

    /**
     * Make authenticated request to ClickUp API
     */
    private function request(string $method, string $endpoint, array $data = [])
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])->$method($this->baseUrl . $endpoint, $data);

        if ($response->failed()) {
            Log::error('ClickUp API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }

    /**
     * Get authenticated user info
     */
    public function getUser(): ?array
    {
        return $this->request('get', '/user');
    }

    /**
     * Get all teams (workspaces)
     */
    public function getTeams(): ?array
    {
        return $this->request('get', '/team');
    }

    /**
     * Get team members
     */
    public function getTeamMembers(string $teamId): ?array
    {
        $teams = $this->getTeams();
        if (!$teams || !isset($teams['teams'])) {
            return null;
        }

        foreach ($teams['teams'] as $team) {
            if ($team['id'] == $teamId) {
                return $team['members'] ?? [];
            }
        }

        return null;
    }

    /**
     * Get all spaces in a team
     */
    public function getSpaces(string $teamId): ?array
    {
        return $this->request('get', "/team/{$teamId}/space");
    }

    /**
     * Get folders in a space
     */
    public function getFolders(string $spaceId): ?array
    {
        return $this->request('get', "/space/{$spaceId}/folder");
    }

    /**
     * Get lists in a folder
     */
    public function getLists(string $folderId): ?array
    {
        return $this->request('get', "/folder/{$folderId}/list");
    }

    /**
     * Get folderless lists in a space
     */
    public function getFolderlessLists(string $spaceId): ?array
    {
        return $this->request('get', "/space/{$spaceId}/list");
    }

    /**
     * Get tasks from a list
     */
    public function getTasks(string $listId, array $params = []): ?array
    {
        $query = http_build_query(array_merge([
            'include_closed' => 'false',
            'subtasks' => 'true',
        ], $params));

        return $this->request('get', "/list/{$listId}/task?{$query}");
    }

    /**
     * Get tasks assigned to a specific user
     */
    private const ACTIVE_STATUSES = ['to do', 'in progress', 'review'];

    public function getTasksByAssignee(string $teamId, string $userId, array $params = []): ?array
    {
        $baseParams = array_merge([
            'include_closed' => 'false',
            'subtasks' => 'true',
        ], $params);
        
        $query = http_build_query($baseParams) . '&assignees[]=' . $userId;

        $response = $this->request('get', "/team/{$teamId}/task?{$query}");
        
        if ($response && isset($response['tasks'])) {
            $response['tasks'] = array_filter($response['tasks'], function ($task) use ($userId) {
                if (empty($task['assignees'])) {
                    return false;
                }
                $isAssigned = false;
                foreach ($task['assignees'] as $assignee) {
                    if ((string) $assignee['id'] === (string) $userId) {
                        $isAssigned = true;
                        break;
                    }
                }
                if (!$isAssigned) {
                    return false;
                }

                $taskStatus = strtolower(data_get($task, 'status.status', ''));
                return in_array($taskStatus, self::ACTIVE_STATUSES);
            });
            $response['tasks'] = array_values($response['tasks']);
        }
        
        return $response;
    }

    /**
     * Get a single task
     */
    public function getTask(string $taskId): ?array
    {
        return $this->request('get', "/task/{$taskId}");
    }

    /**
     * Add assignee to a task
     */
    public function addAssignee(string $taskId, int $userId): ?array
    {
        return $this->request('post', "/task/{$taskId}/assignee/{$userId}");
    }


    /**
     * Get task comments
     */
    public function getTaskComments(string $taskId): ?array
    {
        return $this->request('get', "/task/{$taskId}/comment");
    }

    /**
     * Add comment to task
     */
    public function addTaskComment(string $taskId, string $comment): ?array
    {
        return $this->request('post', "/task/{$taskId}/comment", [
            'comment_text' => $comment,
        ]);
    }

    /**
     * Get time tracked on a task
     */
    public function getTaskTimeTracked(string $taskId): ?array
    {
        return $this->request('get', "/task/{$taskId}/time");
    }

    /**
     * Sync team members to local database
     */
    public function syncTeamMembers(string $teamId): array
    {
        $members = $this->getTeamMembers($teamId);
        if (!$members) {
            return ['success' => false, 'message' => 'Could not fetch team members'];
        }

        $synced = 0;
        foreach ($members as $member) {
            $user = $member['user'];
            
            \App\Models\Employee::updateOrCreate(
                ['clickup_user_id' => $user['id']],
                [
                    'name' => $user['username'] ?? $user['email'],
                    'email' => $user['email'] ?? null,
                    'color' => $user['color'] ?? null,
                    'profile_picture' => $user['profilePicture'] ?? null,
                ]
            );
            $synced++;
        }

        return ['success' => true, 'synced' => $synced];
    }
}
