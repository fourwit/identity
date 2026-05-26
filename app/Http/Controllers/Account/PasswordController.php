<?php

namespace Modules\Identity\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Modules\Identity\Http\Requests\Account\UpdatePasswordRequest;

class PasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request)
    {
        $user = auth()->user();
        $user->password = $request->password;
        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Password updated successfully.');
    }
}
