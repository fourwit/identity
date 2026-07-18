<?php

namespace Modules\Identity\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Identity\DTOs\UserData;
use Modules\Identity\Actions\CreateUserAction;
use Modules\Identity\Actions\UpdateUserAction;
use Modules\Identity\Actions\DeleteUserAction;
use Modules\Identity\Contracts\UserRepositoryInterface;

use Modules\Identity\Http\Requests\Api\DeleteUserRequest;
use Modules\Identity\Http\Requests\Api\StoreUserRequest;
use Modules\Identity\Http\Requests\Api\UpdateUserRequest;

use Modules\Identity\Transformers\UserResource;

use Modules\Identity\Exceptions\ModuleException;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\Model;

class UserController extends BaseApiController
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    protected function resolveUser(Model|int|string $id): Model
    {
        $userId = $id instanceof Model ? (int) $id->getKey() : (int) $id;

        return $this->repository->findByIdOrFail($userId);
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', config('identity.user.per_page', 15));

        $users = $this->repository->search(
            $request->get('search'),      // search term
            $request->get('status'),      // status filter
            $perPage,                           // per page
            $request->get('sort_by'),
            $request->get('sort_dir')
        );

        return $this->paginatedResponse(
            UserResource::collection($users),
            'Users retrieved successfully',
            $users
        );
    }

    public function store(StoreUserRequest $request, CreateUserAction $action)
    {   
        $data = UserData::fromRequest($request);
        $user = $action->execute($data, 'api');

        return $this->successResponse(
            new UserResource($user),
            'User created successfully',
            201
        );
    }

    public function show($id)
    {
        $user = $this->resolveUser($id);

        return $this->successResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    public function update(UpdateUserRequest $request, $id, UpdateUserAction $action)
    {
        $user = $this->resolveUser($id);
        
        $data = UserData::fromRequest($request);
        $user = $action->execute($user, $data, 'api');

        return $this->successResponse(
            new UserResource($user),
            'User updated successfully'
        );
    }

    public function destroy(DeleteUserRequest $request, $id, DeleteUserAction $action)
    {
        $user = $this->resolveUser($id);

        $action->execute($user, 'api');

        return $this->successResponse(null, 'User deleted successfully', 200);
    }
}
