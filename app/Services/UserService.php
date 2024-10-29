<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponseHelper;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user and assign a role.
     *
     * @param array $data
     * @return array
     */
    public function registerUserWithRole(array $data): array
    {
        // Create the user in the repository
        $user = $this->userRepository->create($data);

        // Assign the role if it exists
        $role = $this->userRepository->findRoleById($data['role_id']);
        if ($role) {
            $user->assignRole($role->name);
        }

        // Format the response
        return ApiResponseHelper::formatUserResponse($user);
    }

    /**
     * Authenticate a user and return a JWT token with user information.
     *
     * @param array $credentials
     * @return array|null
     */
    public function loginUser(array $credentials): ?array
    {
        if (!$token = Auth::attempt($credentials)) {
            return null; // Authentication failed
        }

        $user = Auth::user();

        return [
            'user' => ApiResponseHelper::formatUserResponse($user),
            'token' => $this->formatTokenResponse($token)
        ];
    }

    /**
     * Format the token response.
     *
     * @param string $token
     * @return array
     */
    protected function formatTokenResponse($token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ];
    }
}
