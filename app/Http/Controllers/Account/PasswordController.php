<?php

namespace Modules\Identity\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Modules\Identity\Http\Requests\Account\UpdatePasswordRequest;
use Modules\Identity\Services\IdentityManager;

class PasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request, IdentityManager $identity)
    {
        $user = auth()->user();
        $source = $request->is('api/*') || $request->expectsJson() ? 'api' : 'web';
        $identity->updateUserPassword($user, $request->current_password, $request->password, $source);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Password updated successfully.');
    }
}
