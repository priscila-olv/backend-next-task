<?php

namespace App\Http\Controllers;

use App\Services\EmailService;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;

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
            $projects = Project::with([
                'sections' => function ($query) {
                    $query->withCount('tasks');
                }
            ])
                ->select('id', 'description', 'color')
                ->whereHas('users', function ($query) use ($user_id) {
                    $query->where('users.id', $user_id);
                })
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

            $extraFields = $request->except(['description', 'color']);
            if (!empty($extraFields)) {
                $invalidFields = implode(', ', array_keys($extraFields));
                return response()->json(['error' => 'Invalid fields: ' . $invalidFields], 400);
            }

            $project = Project::create([
                'description' => $request['description'],
                'color' => $request['color']
            ]);

            $project->users()->attach($user_id);

            return response()->json(['project' => $project, 'message' => 'Projeto criado com sucesso.'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function remove(Project $project)
    {
        try {
            $user_id = auth()->id();

            if (!$project->users()->where('users.id', $user_id)->exists()) {
                return response()->json(['error' => 'Você não tem permissão para remover este projeto.'], 403);
            }

            $project->users()->detach($user_id);
            $project->delete();

            return response()->json(['message' => 'Projeto removido com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Project $project)
    {
        try {
            $user_id = auth()->id();

            if (!$project->users()->where('users.id', $user_id)->exists()) {
                return response()->json(['error' => 'Você não tem permissão para atualizar este projeto'], 403);
            }

            $project->fill($request->only(['description', 'color']));
            $project->save();

            return response()->json(['project' => $project, 'message' => 'Projeto atualizado com sucesso.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function inviteUserToProject(Request $request, EmailService $emailService)
    {
        try {
            $user_id = auth()->id();
            $user = User::where('id', $user_id)->first();

            $projectId = $request->input('project_id');
            $email = $request->input('email_invite');

            $project = Project::find($projectId);
            if (!$project) {
                return response()->json(['error' => 'Projeto não encontrado'], 404);
            }

            // $invitedUser = User::where('email', $email)->first();
            // if (!$invitedUser) {
            //     return response()->json(['error' => 'Usuário com o email informado não encontrado'], 404);
            // }

            // $project->users()->attach($user_id);

            $mailData = [
                'subject' => 'Convite para projeto NextTask',
                'project_name' => $project->description,
                'name_user' => $user->name
            ];
            $emailService->sendInvitationEmail($email, $project, $mailData);

            return response()->json(['message' => 'Convite enviado com sucesso'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}