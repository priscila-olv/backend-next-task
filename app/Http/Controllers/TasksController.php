<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Section;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        try {
            return Task::all();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user_id = auth()->id();

            $validatedData = $request->validate([
                'name' => 'required',
                'description' => 'nullable',
                'color' => 'nullable',
                'expiration_date' => 'nullable|date',
                'section_id' => 'required|exists:sections,id',
                'priority_id' => 'required|exists:priorities,id'
            ]);

            $section = Section::findOrFail($validatedData['section_id']);
            $project = $section->project;

            $userProject = DB::table('user_projects')
                ->where('user_id', $user_id)
                ->where('project_id', $project->id)
                ->first();

            if (!$userProject) {
                return response()->json(['error' => 'Você não tem permissão para criar tarefas nesse projeto.'], 403);
            }

            $task = Task::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'color' => $validatedData['color'],
                'expiration_date' => $validatedData['expiration_date'],
                'sections_id' => $validatedData['section_id'],
                'priorities_id' => $validatedData['priority_id']
            ]);

            return response()->json(['task' => $task, 'message' => 'Tarefa criada com sucesso.'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function remove(Task $task)
    {
        try {
            $user_id = auth()->id();
            $section = Section::findOrFail($task->sections_id);
            $project = Project::find($section->projects_id);

            $userProject = DB::table('user_projects')
                ->where('user_id', $user_id)
                ->where('project_id', $project->id)
                ->first();

            if (!$userProject) {
                return response()->json(['error' => 'Você não tem permissão para remover tarefas desse projeto.'], 403);
            }

            $task->delete();
            return response()->json(['message' => 'Tarefa removida com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getByIdTask(Task $task)
    {
        try {
            $user_id = auth()->id();
            $section = Section::findOrFail($task->sections_id);
            $project = Project::find($section->projects_id);

            $userProject = DB::table('user_projects')
            ->where('user_id', $user_id)
            ->where('project_id', $project->id)
            ->first();

            if (!$userProject) {
                return response()->json(['error' => 'Você não tem permissão para visualizar esta tarefa.'], 403);
            }

            return response()->json(['task' => $task], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getByIdSection(Section $section)
    {
        try {
            $user_id = auth()->id();
            $project = Project::find($section->projects_id);

            $userProject = DB::table('user_projects')
            ->where('user_id', $user_id)
            ->where('project_id', $project->id)
            ->first();

            if (!$userProject) {
                return response()->json(['error' => 'Você não tem permissão para visualizar as tarefas desta seção.'], 403);
            }

            $tasks = Task::where('sections_id', $section->id)->get();

            return response()->json(['tasks' => $tasks], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Task $task)
    {
        try {
            $user_id = auth()->id();
            $section = Section::findOrFail($task->sections_id);
            $project = Project::find($section->projects_id);

            $userProject = DB::table('user_projects')
            ->where('user_id', $user_id)
            ->where('project_id', $project->id)
            ->first();

            if (!$userProject) {
                return response()->json(['error' => 'Você não tem permissão para visualizar esta tarefa.'], 403);
            }

            $sectionId = $request->input('section_id');
            $priorityId = $request->input('priority_id');

            $section = Section::find($sectionId);
            if (!$section) {
                return response()->json(['error' => 'A seção selecionada não existe.'], 404);
            }

            $task->fill($request->only([
                'name',
                'description',
                'color',
                'expiration_date'
            ]));

            if ($sectionId) {
                $task->sections_id = $sectionId;
            }

            if ($priorityId) {
                $task->priorities_id = $priorityId;
            }

            $task->save();

            return response()->json(['task' => $task, 'message' => 'Tarefa atualizada com sucesso.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
