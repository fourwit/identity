<?php

namespace Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;

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
use Illuminate\Http\RedirectResponse;

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
            $perPage,                           // per page
            $request->get('sort_by'),
            $request->get('sort_dir')
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

    public function edit(Model $user)
    {
        $user = $this->repository->findByIdOrFail((int) $user->getKey());
        return view('identity::admin.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, Model $user, UpdateUserAction $action)
    {
        $user = $this->repository->findByIdOrFail((int) $user->getKey());
        $data = UserData::fromRequest($request);
        $action->execute($user, $data, 'web');

        return $this->redirectBackToListing($request)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, Model $user, DeleteUserAction $action)
    {
        $user = $this->repository->findByIdOrFail((int) $user->getKey());
        $action->execute($user, 'web');

        return $this->redirectBackToListing($request)
            ->with('success', 'User deleted successfully.');
    }

    protected function redirectBackToListing(Request $request): RedirectResponse
    {
        $returnTo = (string) $request->input('redirect_to', '');

        if ($returnTo !== '') {
            return redirect()->to($returnTo);
        }

        return redirect()->route('admin.users.index');
    }
}
