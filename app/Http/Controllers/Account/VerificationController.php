<?php

namespace Modules\Identity\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function status(Request $request)
    {
        $user = auth()->user();
        $phoneVerifiedAt = $user->phone_verified_at ?? $user->identityProfile?->phone_verified_at;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'email_verified' => $user->hasVerifiedEmail(),
                'phone_verified' => !is_null($phoneVerifiedAt),
                'email_verified_at' => $user->email_verified_at,
                'phone_verified_at' => $phoneVerifiedAt,
            ]);
        }

        return view('identity::account.verification', compact('user'));
    }
}
