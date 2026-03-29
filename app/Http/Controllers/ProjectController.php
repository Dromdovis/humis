<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Skill;
use App\Services\ClickUpService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['skills', 'employees'])
            ->orderBy('name')
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $skills = Skill::orderBy('category')->orderBy('name')->get();

        return view('projects.create', compact('skills'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'clickup_list_id' => 'nullable|string',
            'skills' => 'array',
            'skills.*' => 'exists:skills,id',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'client_name' => $validated['client_name'],
            'description' => $validated['description'],
            'clickup_list_id' => $validated['clickup_list_id'],
        ]);

        if (!empty($validated['skills'])) {
            $project->skills()->attach($validated['skills']);
        }

        return redirect()->route('projects.index')
            ->with('success', 'Projektas sukurtas');
    }

    public function show(Project $project)
    {
        $project->load(['skills', 'employees']);
        $allSkills = Skill::orderBy('category')->orderBy('name')->get();

        return view('projects.show', compact('project', 'allSkills'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'clickup_list_id' => 'nullable|string',
            'skills' => 'array',
            'skills.*' => 'exists:skills,id',
        ]);

        $project->update([
            'name' => $validated['name'],
            'client_name' => $validated['client_name'],
            'description' => $validated['description'],
            'clickup_list_id' => $validated['clickup_list_id'],
        ]);

        $project->skills()->sync($validated['skills'] ?? []);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projektas atnaujintas');
    }

    /**
     * Sinchronizuoti projektus iš ClickUp
     */
    public function sync(ClickUpService $clickUpService)
    {
        $teamId = config('services.clickup.team_id');
        
        if (!$teamId) {
            return redirect()->route('projects.index')
                ->with('error', 'CLICKUP_TEAM_ID nenustatytas .env faile');
        }

        $synced = 0;
        $errors = [];

        $spacesResponse = $clickUpService->getSpaces($teamId);
        
        if (!$spacesResponse || !isset($spacesResponse['spaces'])) {
            return redirect()->route('projects.index')
                ->with('error', 'Nepavyko gauti ClickUp spaces');
        }

        foreach ($spacesResponse['spaces'] as $space) {
            $listsResponse = $clickUpService->getFolderlessLists($space['id']);
            if ($listsResponse && isset($listsResponse['lists'])) {
                foreach ($listsResponse['lists'] as $list) {
                    $this->syncProject($list, $space['name']);
                    $synced++;
                }
            }

            $foldersResponse = $clickUpService->getFolders($space['id']);
            if ($foldersResponse && isset($foldersResponse['folders'])) {
                foreach ($foldersResponse['folders'] as $folder) {
                    $folderListsResponse = $clickUpService->getLists($folder['id']);
                    if ($folderListsResponse && isset($folderListsResponse['lists'])) {
                        foreach ($folderListsResponse['lists'] as $list) {
                            $this->syncProject($list, $folder['name']);
                            $synced++;
                        }
                    }
                }
            }
        }

        if ($synced > 0) {
            return redirect()->route('projects.index')
                ->with('success', "Sinchronizuota {$synced} projektų iš ClickUp");
        }

        return redirect()->route('projects.index')
            ->with('warning', 'Nerasta projektų sinchronizavimui');
    }

    /**
     * Sukurti arba atnaujinti projektą iš ClickUp list
     */
    private function syncProject(array $list, string $clientOrFolder = null): Project
    {
        return Project::updateOrCreate(
            ['clickup_list_id' => $list['id']],
            [
                'name' => $list['name'],
                'client_name' => $clientOrFolder,
                'description' => null,
            ]
        );
    }
}
