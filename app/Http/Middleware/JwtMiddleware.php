<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Usuário não autenticado'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Falha na autenticação'], 401);
        }

        return $next($request);
    }
}
