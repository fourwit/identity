<?php

namespace Modules\Identity\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Identity\Models\User;
use Modules\Identity\Http\Requests\Api\StoreUserRequest;
use Modules\Identity\Http\Requests\Api\UpdateUserRequest;
use Modules\Identity\Transformers\UserResource;
use Modules\Identity\Services\ActivityLogger;
use Illuminate\Http\Request;
use Exception;

class UserController extends BaseApiController
{
    private $source = "api";
    
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', config('identity.per_page', 15));
        $users = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => UserResource::collection($users),
            'pagination' => [
                'current_page'   => $users->currentPage(),
                'per_page'       => $users->perPage(),
                'total'          => $users->total(),
                'last_page'      => $users->lastPage(),
                'next_page_url'  => $users->nextPageUrl(),
                'prev_page_url'  => $users->previousPageUrl(),
            ]
        ]);
    }

    public function store(StoreUserRequest $request)
    {   
        $user = User::create($request->validated());

        // Log Activity
        ActivityLogger::log(
            "Created new user: {$user->name}",
            $user,
            ['email' => $user->email, 'status' => $user->status],
            'created',
            $this->source
        );

        return $this->successResponse(
            new UserResource($user),
            'User created successfully',
            201
        );
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        return $this->successResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        $oldData = $user->only(['name', 'email', 'status']);
        $user->update($request->validated());

        // Log Activity
        ActivityLogger::log(
            "Updated user: {$user->name}",
            $user,
            ['old' => $oldData, 'new' => $user->only(['name', 'email', 'status'])],
            'updated',
            $this->source
        );

        return $this->successResponse(
            new UserResource($user),
            'User updated successfully'
        );
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        $userName = $user->name;
        $user->delete();

        // Log Activity
        ActivityLogger::log(
            "Deleted user: {$userName}",
            null,
            ['deleted_user_id' => $id, 'name' => $userName],
            'deleted',
            $this->source
        );

        return $this->successResponse(null, 'User deleted successfully', 200);
    }
}