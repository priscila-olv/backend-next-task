<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionsController extends Controller
{
    public function index()
    {
        try {
            $user_id = auth()->id();
            $sections = Section::whereHas('project', function ($query) use ($user_id) {
                $query->where('users_id', $user_id);
            })->get();

            return response()->json(['sections' => $sections], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user_id = auth()->id();

            $description = $request->input('description');
            $project_id = $request->input('project_id');

            // $validatedData = $request->validate([
            //     'description' => 'required',
            //     'project_id' => 'required|exists:projects,id'
            // ]);

            //$project = Project::find($validatedData['project_id']);

            // if ($project->users_id != $user_id) {
            //     return response()->json(['error' => 'Você não tem permissão para criar sessões nesse projeto.'], 403);
            // }

            // $section = Section::create([
            //     'description' => $validatedData['description'],
            //     'projects_id' => $validatedData['project_id']
            // ]);
            $section = Section::create([
                'description' => $description,
                'projects_id' => $project_id
            ]);

            return response()->json(['section' => $section, 'message' => 'Sessão criada com sucesso.'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function remove(Section $section)
    {
        try {
            $user_id = auth()->id();
            $project = Project::find($section->projects_id);

            if ($project->users_id != $user_id) {
                return response()->json(['error' => 'Você não tem permissão para remover sessões desse projeto.'], 403);
            }
            $section->delete();
            return response()->json(['message' => 'Sessão removida com sucesso!'], 200);

        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getAllProjectSections(Request $request)
    {
        try {
            $projectId = $request->route('project');

            $sections = Section::where('projects_id', $projectId)->with('tasks')->get();

            return response()->json(['sections' => $sections], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, Section $section)
    {
        try {
            $user_id = auth()->id();
            $project = Project::find($section->projects_id);

            if ($project->users_id != $user_id) {
                return response()->json(['error' => 'Você não tem permissão para atualizar esta seção.'], 403);
            }

            $validatedData = $request->validate([
                'description' => 'sometimes',
            ]);

            $section->update($validatedData);

            return response()->json(['section' => $section, 'message' => 'Seção atualizada com sucesso.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}