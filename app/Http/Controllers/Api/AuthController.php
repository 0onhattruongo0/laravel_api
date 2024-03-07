<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        $check = Auth::attempt([
            'email' => $email,
            'password' => $password
        ]);

        if ($check) {
            /** @var \App\Models\MyUserModel $user **/
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'status' => 200,
                'token' => $token
            ];
        } else {
            return [
                'status' => 401,
                'token' => 'unauthorized'
            ];
        }
    }

    public function deleteToken(Request $request)
    {
        return $request->user()->currentAccessToken()->delete(); // Phải đăng nhập mới có user -> thiết lập middleware
    }

    public function refreshIoken(Request $request)
    {
        if ($request->header('authorization')) {
            $hashToken = $request->header('authorization');
            $hashToken = str_replace('Bearer', '', $hashToken);
            $hashToken = trim($hashToken);

            $token = PersonalAccessToken::findToken($hashToken);
            if ($token) {
                $tokenCreated = $token->created_at;

                $expire = Carbon::parse($tokenCreated)->addMinutes(config('sanctum.expiration'));
                if (Carbon::now() >= $expire) {
                    $userId = $token->tokenable_id;
                    $user = User::find($userId);

                    $user->token()->delete();

                    $newToken = $user->createToken('auth_token')->plainTextToken;

                    $response = [
                        'status' => 200,
                        'token' => $newToken
                    ];
                } else {
                    $response = [
                        'status' => 200,
                        'title' => 'unexpired'
                    ];
                }
            } else {
                $response = [
                    'status' => 401,
                    'title' => 'Unauthorized'
                ];
            }

            return $response;
        }
    }
}
