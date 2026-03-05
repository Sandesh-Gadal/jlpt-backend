<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // ── POST /api/v1/auth/forgot-password ──────────────────
    public function forgot(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Always return success to prevent email enumeration
        if (!$user) {
            return response()->json([
                'message' => 'If that email exists, a reset link has been sent.',
            ]);
        }

        // Generate token and store directly on user
        $token = Str::random(64);

        $user->update([
            'password_reset_token'      => Hash::make($token),
            'password_reset_expires_at' => now()->addMinutes(60),
        ]);

        // Send email
        $this->sendResetEmail($user, $token);

        return response()->json([
            'message' => 'If that email exists, a reset link has been sent.',
        ]);
    }

    // ── POST /api/v1/auth/reset-password ───────────────────
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => 'required|confirmed|min:8',
        ]);

        $user = User::where('email', $request->email)->first();

        // User not found
        if (!$user) {
            return response()->json([
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // No reset token exists
        if (!$user->password_reset_token) {
            return response()->json([
                'message' => 'No password reset was requested.',
            ], 400);
        }

        // Token expired
        if (now()->isAfter($user->password_reset_expires_at)) {
            $user->update([
                'password_reset_token'      => null,
                'password_reset_expires_at' => null,
            ]);

            return response()->json([
                'message' => 'Reset token has expired. Please request a new one.',
            ], 400);
        }

        // Token does not match
        if (!Hash::check($request->token, $user->password_reset_token)) {
            return response()->json([
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // All good — update password and clear token
        $user->update([
            'password_hash'             => Hash::make($request->password),
            'password_reset_token'      => null,
            'password_reset_expires_at' => null,
        ]);

        // Revoke all existing sessions
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password reset successfully. Please log in with your new password.',
        ]);
    }

    // ── Private: send reset email ──────────────────────────
    private function sendResetEmail(User $user, string $token): void
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $resetUrl    = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

        Mail::send([], [], function ($message) use ($user, $resetUrl) {
            $message
                ->to($user->email, $user->full_name)
                ->subject('Reset Your JLPT Master Password')
                ->html($this->resetEmailHtml($user->full_name, $resetUrl));
        });
    }

    // ── Private: email HTML ────────────────────────────────
    private function resetEmailHtml(string $name, string $resetUrl): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 40px;">
            <div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 10px; padding: 40px;">
                <h1 style="color: #1A56DB;">🌸 JLPT Master</h1>
                <h2 style="color: #111827;">Reset Your Password</h2>
                <p>Hi {$name},</p>
                <p>Click the button below to reset your password.</p>
                <a href="{$resetUrl}"
                   style="display:inline-block; background:#1A56DB; color:white; padding:12px 28px;
                          border-radius:8px; text-decoration:none; font-weight:bold; margin:20px 0;">
                    Reset Password
                </a>
                <p style="color:#6B7280; font-size:13px;">Expires in 60 minutes.</p>
                <p style="color:#6B7280; font-size:13px;">If you didn't request this, ignore this email.</p>
            </div>
        </body>
        </html>
        HTML;
    }
}