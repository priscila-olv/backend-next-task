<?php

namespace App\Http\Controllers;

use App\Models\InviteUserProject;
use App\Services\EmailService;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                'users' => function ($query) {
                    $query->select('users.id', 'users.email');
                },
                'inviteUserProjects' => function ($query) {
                    $query->select('invite_projects.id', 'invite_projects.user_email', 'invite_projects.project_id')
                        ->with('project:id,description,color');
                },
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
    
                $userParticipating = $project->users->pluck('email')->toArray();
                if (count($userParticipating) > 1) {
                    $project->user_participating = $userParticipating;
                } else {
                    $project->user_participating = [];
                }
    
                $userInvitePending = $project->inviteUserProjects->pluck('user_email')->toArray();
                $project->user_invite_pending = $userInvitePending;
            }
    
            $projects->makeHidden(['sections', 'users', 'inviteUserProjects']);
    
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

            $validatedData = $request->validate([
                'description' => 'required',
                'color' => 'sometimes',
            ]);

            $project = Project::create($validatedData);

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
    public function inviteUserToProject(Request $request)
    {
        try {
            $user_id = auth()->id();

            $projectId = $request->input('project_id');
            $emails = $request->input('emails_invite');

            $project = Project::find($projectId);
            if (!$project) {
                return response()->json(['error' => 'Projeto não encontrado'], 404);
            }

            $token = strtoupper(Str::random(8));

            $emailService = new EmailService();

            $isUserInProject = DB::table('user_projects')
                ->where('user_id', $user_id)
                ->where('project_id', $projectId)
                ->exists();

            InviteUserProject::where('project_id', $projectId)
                ->whereNotIn('user_email', $emails)
                ->where('user_email', '!=', auth()->user()->email)
                ->delete();

            DB::table('user_projects')
                ->where('project_id', $projectId)
                ->whereIn('user_id', function ($query) use ($emails) {
                    $query->select('id')
                        ->from('users')
                        ->whereIn('email', $emails);
                })
                ->where('user_id', '!=', $user_id)
                ->delete();

            foreach ($emails as $email) {

                $user = User::where('email', $email)->first();

                if ($user != null) {
                    $isUserInProject = DB::table('user_projects')
                        ->where('user_id', $user->id)
                        ->where('project_id', $projectId)
                        ->exists();
                } else {
                    $isUserInProject = false;
                }
                $existingInvite = InviteUserProject::where('project_id', $projectId)
                    ->where('user_email', $email)
                    ->exists();

                if (!$isUserInProject && !$existingInvite) {

                    $mailData = [
                        'subject' => 'Convite para projeto NextTask',
                        'project_name' => $project->description,
                        'name_user' => auth()->user()->name,
                        'token_invite' => $token
                    ];

                    $inviteUserProject = new InviteUserProject();
                    $inviteUserProject->project_id = $projectId;
                    $inviteUserProject->user_email = $email;
                    $inviteUserProject->token_invite = $token;
                    $inviteUserProject->save();

                    $emailService->sendInvitationEmail($email, $project, $mailData);
                }
            }

            return response()->json(['message' => 'Convite enviado com sucesso'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeShared(Request $request)
    {
        try {
            $user_id = auth()->id();

            $extraFields = $request->except(['description', 'color']);
            if (!empty($extraFields)) {
                $invalidFields = implode(', ', array_keys($extraFields));
                return response()->json(['error' => 'Invalid fields: ' . $invalidFields], 400);
            }

            $token = strtoupper(Str::random(8));
            while (Project::where('token_invite', $token)->exists()) {
                $token = strtoupper(Str::random(8));
            }

            $project = Project::create([
                'description' => $request['description'],
                'color' => $request['color'],
                'token_invite' => $token
            ]);

            $project->users()->attach($user_id);

            return response()->json(['project' => $project, 'message' => 'Projeto criado com sucesso.'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function participateProject(Request $request)
    {
        try {
            $user_id = auth()->id();
            $tokenInvite = $request->input('token_invite');
            $project = Project::join('invite_projects', 'projects.id', '=', 'invite_projects.project_id')
                ->where('invite_projects.token_invite', $tokenInvite)
                ->select('projects.*')
                ->first();

            if (!$project) {
                return response()->json(['error' => 'Projeto com código informado não existe'], 404);
            }

            $existingRecord = DB::table('user_projects')
                ->where('user_id', $user_id)
                ->where('project_id', $project->id)
                ->first();

            if ($existingRecord) {
                return response()->json(['message' => 'Você já participa deste projeto'], 400);
            }

            $invitation = DB::table('invite_projects')
                ->where('project_id', $project->id)
                ->where('user_email', auth()->user()->email)
                ->first();

            if (!$invitation) {
                return response()->json(['error' => 'Você não foi convidado para participar desse projeto'], 403);
            }

            DB::table('user_projects')->insert([
                'user_id' => $user_id,
                'project_id' => $project->id,
            ]);

            DB::table('invite_projects')
                ->where('project_id', $project->id)
                ->where('user_email', auth()->user()->email)
                ->delete();

            return response()->json(['project' => $project, 'message' => 'Você foi adicionado ao projeto'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}