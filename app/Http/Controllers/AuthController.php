<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

use Illuminate\Auth\Access\AuthorizationException;

use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function generateToken(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => 'Credenciais inválidas'], 400);
        }
        
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciais inválidas'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Falha ao criar o token'], 500);
        }
        
        return response()->json(compact('token'));
    }


    public function isAuth(Request $request)
    {
        $idUser = auth()->id();
        $user = User::findOrFail($idUser);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    

        return response()->json(['data' => $userData, 'message' => 'Usuário autenticado', 'isAuth' => 'true']);
    }
    
}
