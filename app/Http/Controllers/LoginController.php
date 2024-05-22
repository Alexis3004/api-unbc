<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $token = $request->user()->createToken('auth-token', ['*'], now()->addMinutes(PersonalAccessToken::$TIEMPO_EXPIRACION))->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $request->user(),
            ], 200);
        }

        return response()->json([
            'estado' => false,
            'mensaje' => 'Acceso no autorizado'
        ], 401);
    }

    public function logout(Request $request)
    {
        // Remove current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'estado' => true,
            'mensaje' => 'Sesión cerrada'
        ], 200);
    }

    public function logoutAll(Request $request)
    {
        // Remove all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'estado' => true,
            'mensaje' => 'Sesiones cerradas'
        ], 200);
    }
}
