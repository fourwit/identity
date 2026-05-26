<?php

namespace Modules\Identity\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Http\Requests\Account\UpdateProfileRequest;
use Modules\Identity\Transformers\UserResource;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        return view('identity::account.profile', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        
        $user->update($request->only([
            'name', 'first_name', 'last_name', 'email', 'phone', 'username', 'timezone', 'locale'
        ]));

        if ($request->expectsJson()) {
            return new UserResource($user);
        }

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function me(Request $request)
    {
        return new UserResource(auth()->user());
    }

    public function removeAvatar(Request $request)
    {
        $user = auth()->user();
        $user->avatar_id = null;
        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Avatar reference removed successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Avatar removed successfully.');
    }
}
