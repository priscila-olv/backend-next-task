<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmationMail;
use App\Models\EmailConfirmation;
use App\Services\EmailService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersController extends Controller
{
    public function index()
    {
        $this->middleware('jwt.auth');

        try {
            return User::all();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $email = $request->input("email");

            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                return response()->json(['error' => 'Email informado já está cadastrado no sistema.'], 400);
            }

            $user = User::create([
                "name" => $request->input("name"),
                "email" => $email,
                "password" => $request->input("password")
            ]);

            $extraFields = $request->except(['name', 'email', 'password']);
            if (!empty($extraFields)) {
                $invalidFields = implode(', ', array_keys($extraFields));
                return response()->json(['error' => 'Invalid fields: ' . $invalidFields], 400);
            }

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];

            return response()->json(['user' => $userData, 'message' => 'Usuário criado com sucesso'], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, User $user)
    {
        $this->middleware('jwt.auth');

        try {
            if (auth()->user()->id !== $user->id) {
                return response()->json(['error' => 'Você não tem permissão para atualizar este perfil'], 403);
            }

            $newEmail = $request->input('email');

            if ($newEmail !== $user->email && User::where('email', $newEmail)->exists()) {
                return response()->json(['error' => 'Email informado já está cadastrado no sistema.'], 400);
            }

            $user->name = $request->input('name');
            $user->email = $request->input('email');

            $extraFields = $request->except(['name', 'email']);
            if (!empty($extraFields)) {
                $invalidFields = implode(', ', array_keys($extraFields));
                return response()->json(['error' => 'Invalid fields: ' . $invalidFields], 400);
            }
            $user->save();

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];

            return response()->json(['user' => $userData, 'message' => 'Usuário modificado com sucesso'], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function remove(User $user)
    {
        $this->middleware('jwt.auth');

        try {
            $user->delete();
            return response()->json(['message' => 'Usuário deletado com sucesso!'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function find($id)
    {
        $this->middleware('jwt.auth');

        try {
            $user = User::findOrFail($id);
            return response()->json(['user' => $user]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->middleware('jwt.auth');

        try {
            if (auth()->user()->id !== $user->id) {
                return response()->json(['error' => 'Você não tem permissão para atualizar este perfil'], 403);
            }
            
            $oldPassword = $request->input('old_password');
            $newPassword = $request->input('new_password');
            $confirmPassword = $request->input('confirm_password');

            if (!password_verify($oldPassword, $user->password)) {
                return response()->json(['error' => 'Senha antiga incorreta.'], 400);
            }

            if ($newPassword !== $confirmPassword) {
                return response()->json(['error' => 'A nova senha não coincide com a confirmação.'], 400);
            }

            $user->password = $newPassword;
            $user->save();

            return response()->json(['message' => 'Senha alterada com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
}