<?php

namespace Modules\Identity\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Http\Requests\Account\UpdateProfileRequest;
use Modules\Identity\Services\IdentityManager;
use Modules\Identity\Transformers\UserResource;

class ProfileController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function show()
    {
        $user = $this->repository->findByIdOrFail((int) auth()->id());
        return view('identity::account.profile', compact('user'));
    }

    public function update(UpdateProfileRequest $request, IdentityManager $identity)
    {
        $user = $this->repository->findByIdOrFail((int) auth()->id());
        $user = $identity->updateAccountProfile($user, $request->only([
            'name', 'first_name', 'last_name', 'email', 'phone', 'username', 'timezone', 'locale'
        ]), 'web');

        if ($request->expectsJson()) {
            return new UserResource($user);
        }

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function me(Request $request)
    {
        $user = $this->repository->findByIdOrFail((int) auth()->id());
        return new UserResource($user);
    }

    public function removeAvatar(Request $request)
    {
        $user = $this->repository->findByIdOrFail((int) auth()->id());
        $this->repository->update($user, ['avatar_id' => null]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Avatar reference removed successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Avatar removed successfully.');
    }

    public function destroy(Request $request, IdentityManager $identity)
    {
        $request->validate([
            'delete_password' => ['required', 'current_password'],
        ], [], [
            'delete_password' => 'password',
        ]);

        $user = $this->repository->findByIdOrFail((int) auth()->id());
        $identity->deleteOwnAccount($user, $request->string('delete_password')->toString(), 'web');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your account has been deleted successfully.'
            ]);
        }

        return redirect('/')->with('success', 'Your account has been deleted successfully.');
    }
}
