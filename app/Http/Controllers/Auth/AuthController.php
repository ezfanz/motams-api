<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\UserService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Auth\LoginUserRequest;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(RegisterUserRequest $request)
    {
        $user = $this->userService->registerUserWithRole($request->all());
        return ResponseHelper::success($user, 'User created successfully', 201);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @param LoginUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginUserRequest $request)
    {
        $credentials = $request->only(['username', 'password']);

        try {
            // Step 1: Check Active Directory authentication if bypass is disabled
            $ADUser = null;
            if (!config('app.ad_bypass', false)) {
                $ADUser = $this->userService->activeDirectoryAuthenticateAndRetrieve($credentials);

                if (!$ADUser) {
                    return ResponseHelper::error('Active Directory authentication failed', 401);
                }

                // Sync user data with AD and ensure it exists locally
                $this->userService->syncActiveDirectoryUser($ADUser);
            }

            // Step 2: Proceed with the existing JWT login flow, passing ADUser for validation
            $jwtResponse = $this->userService->loginUser($credentials, $ADUser);

            if (!$jwtResponse) {
                return ResponseHelper::error('Invalid username or password', 401);
            }

            return ResponseHelper::success($jwtResponse, 'Login successful');
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
