<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Application\Services\UserService;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role_id', 'department_id']);
        $perPage = $request->get('per_page', 10);

        $users = $this->userService->index($filters, $perPage);

        return $this->responsePagination(
            $users,
            UserResource::collection($users->items()),
            'Users retrieved successfully'
        );
    }
    public function store(UserStoreRequest $request)
    {
        $authUser = $request->user();
        $data = $request->validated();

        $user = $this->userService->store($data, $authUser);

        return $this->success(new UserResource($user), 'User created successfully', 201);
    }
    public function show(User $user)
    {
        $user = $this->userService->show($user);
        return $this->success(new UserResource($user), 'User retrieved successfully');
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $authUser = $request->user();
        $data = $request->validated();

        $user = $this->userService->update($user, $data, $authUser);

        return $this->success(new UserResource($user), 'User updated successfully');
    }

    public function destroy(User $user, Request $request)
    {   
        $authUser = $request->user();
        $this->userService->delete($user, $authUser);
        return $this->success([], 'User deleted successfully');
    }
}
