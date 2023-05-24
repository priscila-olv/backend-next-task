<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmationMail;
use App\Models\EmailConfirmation;
use App\Services\EmailService;
use Google\Client;
use Google\Service\Oauth2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
    public function sendEmailConfirmation(Request $request, EmailService $emailService)
    {
        try {
            $email = $request->input('email');
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(["error" => "Email informado não possui cadastro no sistema"], 400);
            }

            $mailData = [
                'subject' => 'Redefinição de senha Next Task',
                'name' => $user->name,
            ];

            $emailService->sendConfirmationEmail($user, $email, $mailData);

            return response()->json(["message" => "Código enviado para {$email}. Informe o código recebido para criar uma nova senha"], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function resetPassword(Request $request)
    {
        try {
            $token = $request->input('token');
            $email = $request->input('email');
            $password = $request->input('password');
            $confirmPassword = $request->input('confirm_password');

            if (empty($token) || empty($email) || empty($password) || empty($confirmPassword)) {
                return response()->json(['error' => 'Todos os campos devem ser preenchidos'], 400);
            }

            if ($password !== $confirmPassword) {
                return response()->json(['error' => 'As senhas não coincidem'], 400);
            }

            $emailConfirmation = EmailConfirmation::whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            })->first();

            if (!$emailConfirmation) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            $storedToken = $emailConfirmation->token;

            if (!password_verify($token, $storedToken)) {
                return response()->json(['error' => 'Token inválido'], 400);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            $user->password = $password;
            $user->save();

            EmailConfirmation::whereHas('user', function ($query) use ($user) {
                $query->where('id', $user->id);
            })->delete();

            return response()->json(['message' => 'Senha redefinida com sucesso']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logout realizado com sucesso']);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'O token já expirou'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Falha ao fazer logout'], 500);
        }
    }
    public function googleLogin(Request $request)
{
    $client = new Client();
    $client->setAuthConfig(env('GOOGLE_CLIENT_SECRET'));
    $client->setRedirectUri($request->getSchemeAndHttpHost() . '/google/callback');
    $client->addScope('email');
    $client->addScope('profile');

    if ($request->has('code')) {
        $client->fetchAccessTokenWithAuthCode($request->input('code'));
        $accessToken = $client->getAccessToken();

        $oauth2 = new Oauth2($client);
        $userInfo = $oauth2->userinfo->get();
        $email = $userInfo->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $userInfo->name,
                'email' => $email,
                'password' => bcrypt(Str::random(16)), 
            ]);
        }

        Auth::login($user);

        return redirect('/home');
    } else {
        $authUrl = $client->createAuthUrl();
        return redirect($authUrl);
    }
}

}