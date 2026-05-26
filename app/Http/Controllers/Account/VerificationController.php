<?php

namespace Modules\Identity\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function status(Request $request)
    {
        $user = auth()->user();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'email_verified' => $user->hasVerifiedEmail(),
                'phone_verified' => !is_null($user->phone_verified_at),
                'email_verified_at' => $user->email_verified_at,
                'phone_verified_at' => $user->phone_verified_at,
            ]);
        }

        return view('identity::account.verification', compact('user'));
    }
}
