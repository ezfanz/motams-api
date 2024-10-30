<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use App\Helpers\ResponseHelper;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getAllUsers();
        return ResponseHelper::success($users, 'Users retrieved successfully');
    }

    public function show($id)
    {
        $user = $this->userService->getUserById($id);
        return ResponseHelper::success($user, 'User retrieved successfully');
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->registerUserWithRole($request->all());
        return ResponseHelper::success($user, 'User created successfully', 201);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->userService->updateUser($id, $request->all());
        return ResponseHelper::success($user, 'User updated successfully');
    }

    public function destroy($id)
    {
        $this->userService->deleteUser($id);
        return ResponseHelper::success(null, 'User deleted successfully');
    }
}
