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
            $projects = Project::where('users_id', $user_id)->get();
            return $projects;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $user_id = auth()->id();

            $extraFields = $request->except(['description']);
            if (!empty($extraFields)) {
                $invalidFields = implode(', ', array_keys($extraFields));
                return response()->json(['error' => 'Invalid fields: ' . $invalidFields], 400);
            }

            $project = Project::create([
                'description' => $request['description'],
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

}