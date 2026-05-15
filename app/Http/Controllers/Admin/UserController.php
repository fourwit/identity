<?php

namespace Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Identity\Models\User;

use Modules\Identity\DTOs\UserData;
use Modules\Identity\Actions\CreateUserAction;
use Modules\Identity\Actions\UpdateUserAction;
use Modules\Identity\Actions\DeleteUserAction;

use Modules\Identity\Http\Requests\Admin\StoreUserRequest;
use Modules\Identity\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $source = "web";
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

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(15);

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