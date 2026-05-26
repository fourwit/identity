<?php

namespace Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Identity\Models\User;

use Modules\Identity\DTOs\UserData;
use Modules\Identity\Actions\CreateUserAction;
use Modules\Identity\Actions\UpdateUserAction;
use Modules\Identity\Actions\DeleteUserAction;


use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Http\Requests\Admin\StoreUserRequest;
use Modules\Identity\Http\Requests\Admin\UpdateUserRequest;

use Modules\Identity\Exceptions\ModuleException;
use Modules\Identity\Exceptions\UserNotFoundException;

use Illuminate\Http\Request;

class UserController extends Controller
{
    private $source = "web";

    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', config('identity.user.per_page', 15));

        $users = $this->repository->search(
            $request->get('search'),      // search term
            $request->get('status'),      // status filter
            $perPage                            // per page
        );
        // dd($users->toArray()); // ← TEMPORARY: Check what users are returned
        return view('identity::admin.index', compact('users'));
    }

    public function create()
    {
        return view('identity::admin.create');
    }

    public function store(StoreUserRequest $request, CreateUserAction $action)
    {
        
        $data = UserData::fromRequest($request);
        $user = $action->execute($data, 'web');

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('identity::admin.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $action)
    {
        $data = UserData::fromRequest($request);
        $action->execute($user, $data, 'web');

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user, DeleteUserAction $action)
    {
        $action->execute($user, 'web');

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}