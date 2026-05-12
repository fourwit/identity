<?php

namespace Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Identity\Models\User;
use Modules\Identity\Http\Requests\Admin\StoreUserRequest;
use Modules\Identity\Http\Requests\Admin\UpdateUserRequest;
use Modules\Identity\Services\ActivityLogger;
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

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        // Log Activity (Web)
        ActivityLogger::log(
            "Created new user: {$user->name}",
            $user,
            ['email' => $user->email, 'status' => $user->status],
            'created',
            $this->source
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('identity::admin.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $oldData = $user->only(['name', 'email', 'status']);
        $user->update($request->validated());

        // Log Activity (Web)
        ActivityLogger::log(
            "Updated user: {$user->name}",
            $user,
            ['old' => $oldData, 'new' => $user->only(['name', 'email', 'status'])],
            'updated',
            $this->source
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $userName = $user->name;
        $user->delete();

        // Log Activity (Web)
        ActivityLogger::log(
            "Deleted user: {$userName}",
            null,
            ['deleted_user_id' => $user->id, 'name' => $userName],
            'deleted',
            $this->source
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}