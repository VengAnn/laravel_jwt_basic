<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

use App\Models\InvalidatedToken;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users'),
            ],
            'password' => 'required|string|min:8',
        ], [
            'email.unique' => 'Email already exists',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                "password" => Hash::make($request->password),
            ]);

            $userDetails = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];

            return response()->json(['user' => $userDetails], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Authenticate a user and return a JWT.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            $token = JWTAuth::attempt($credentials);

            if (!$token) {
                return response()->json(['error' => 'Invalid Email or Password'], 401);
            }

            $user = Auth::user();
            $userDetails = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];

            return response()->json(['user' => $userDetails, 'token' => $token], 200);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * current user logined
     */
    public function me()
    {
        try {
            $user = Auth::user();

            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token);
            $jti = $payload->get('jti');

            // Check if the token is invalidated ( haveing in the table invalidated token ) 
            if (InvalidatedToken::where('jti', $jti)->exists()) {
                return response()->json(['error' => 'Token has been invalidated'], 401);
            }

            if (!$user) {
                return response()->json(['error' => 'User not found or token invalid'], 401);
            }

            $userDetails = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
            return response()->json($userDetails);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired', 'message' => $e->getMessage()], 401);

        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid', 'message' => $e->getMessage()], 401);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Token not provided', 'message' => $e->getMessage()], 401);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        try {
            // Get the current token
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token);

            // Convert Unix timestamp to Carbon datetime
            $expiredTime = Carbon::createFromTimestamp($payload->get('exp'));

            // save all token logout to ( table invalidated token )
            InvalidatedToken::create([
                'jti' => $payload->get('jti'),
                'expired_time' => $expiredTime,
            ]);

            Auth::logout();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Logout failed', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Refresh a JWT.
     */
    public function refresh()
    {
        try {
            // Get the current token
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token);

            // Convert Unix timestamp to Carbon datetime
            $expiredTime = Carbon::createFromTimestamp($payload->get('exp'));

            // save all token logout to ( table invalidated token )
            InvalidatedToken::create([
                'jti' => $payload->get('jti'),
                'expired_time' => $expiredTime,
            ]);

            $newToken = Auth::refresh();
            return response()->json(['token' => $newToken]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token', 'message' => $e->getMessage()], 500);
        }
    }
}
