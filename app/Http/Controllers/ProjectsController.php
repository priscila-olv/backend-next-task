<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;


class ProjectsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        try {
            $user_id = auth()->id();
            $projects = Project::with(['sections' => function ($query) {
                    $query->withCount('tasks');
                }])
                ->select('id', 'description', 'users_id')
                ->where('users_id', $user_id)
                ->get();
    
            foreach ($projects as $project) {
                $project->count_tasks = $project->sections->sum('tasks_count');
            }
            $projects->makeHidden('sections');

            return $projects;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $user_id = auth()->id();

            $extraFields = $request->except(['description','color']);
            if (!empty($extraFields)) {
                $invalidFields = implode(', ', array_keys($extraFields));
                return response()->json(['error' => 'Invalid fields: ' . $invalidFields], 400);
            }

            $project = Project::create([
                'description' => $request['description'],
                'color' => $request['color'],
                'users_id' => $user_id
            ]);

            return response()->json(['project' => $project, 'message' => 'Projeto criado com sucesso.'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function remove(Project $project)
    {
        try {
            $user_id = auth()->id();

            if ($project->users_id != $user_id) {
                return response()->json(['error' => 'Você não tem permissão para remover este projeto.'], 403);
            }

            $project->delete();
            return response()->json(['message' => 'Projeto removido com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, Project $project)
    {
        $this->middleware('jwt.auth');

        if (auth()->user()->id !== $project->users_id) {
            return response()->json(['error' => 'Você não tem permissão para atualizar este projeto'], 403);
        }
        try {
            $project->description = $request->input('description');
            $project->color = $request->input('color');

            $extraFields = $request->except(['description', 'color']);
            if (!empty($extraFields)) {
                $invalidFields = implode(', ', array_keys($extraFields));
                return response()->json(['error' => 'Invalid fields: ' . $invalidFields], 400);
            }
            $project->save();

            return response()->json(['project' => $project, 'message' => 'Projeto modificado com sucesso'], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}