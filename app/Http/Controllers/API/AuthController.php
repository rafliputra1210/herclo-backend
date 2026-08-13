<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required', // Ini adalah Kode Dashboard dari frontend
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('herclo_token')->plainTextToken;
            
            return response()->json([
                'message' => 'Berhasil masuk',
                'token' => $token,
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'Email atau Kode Dashboard salah!'], 401);
    }
}