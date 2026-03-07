<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    // ── GET /api/v1/auth/verify-email/{id}/{hash} ──────────
    // public function verify(Request $request, string $id, string $hash): JsonResponse
    // {

          public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        if (! URL::hasValidSignature($request)) {
            return redirect()->away(config('app.frontend_url') . '/auth/verify-email/error?reason=invalid-signature');
        }

        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return redirect()->away(config('app.frontend_url') . '/auth/verify-email/error?reason=invalid-hash');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->away(config('app.frontend_url') . '/auth/verify-email/success');
    }
        // $user = User::findOrFail($id);

        // // Check hash matches user email
        // if (!hash_equals(sha1($user->email), $hash)) {
        //     return response()->json([
        //         'message' => 'Invalid or expired verification link.',
        //     ], 400);
        // }

        // // Already verified
        // if ($user->email_verified) {
        //     return response()->json([
        //         'message' => 'Email already verified.',
        //     ]);
        // }

        // // Mark as verified
        // $user->update([
        //     'email_verified'    => true,
        //     'email_verified_at' => now(),
        // ]);

        // event(new Verified($user));

        // return response()->json([
        //     'message' => 'Email verified successfully. You can now log in.',
        // ]);
    // }

    // ── POST /api/v1/auth/resend-verification ──────────────
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified) {
            return response()->json([
                'message' => 'Your email is already verified.',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent. Please check your inbox.',
        ]);
    }
}