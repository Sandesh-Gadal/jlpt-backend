<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    // ── POST /api/v1/auth/register ─────────────────────────
    public function register(Request $request): JsonResponse
    {

        // dd("i m inside the api call ");
        $data = $request->validate([
            'full_name'          => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'password'           => ['required', 'confirmed', Password::min(8)],
            'jlpt_target_level'  => 'nullable|in:N1,N2,N3,N4,N5',
            'tenant_name'        => 'nullable|string|max:255',
        ]);

        // Get the Free plan
        $freePlan = Plan::where('plan_type', 'free')->first();

        // Create tenant
        $tenant = Tenant::create([
            'name'        => $data['tenant_name'] ?? $data['full_name'],
            'tenant_type' => 'individual',
            'plan_id'     => $freePlan?->id,
            'max_seats'   => 1,
            'status'      => 'free',
        ]);

        // Create user
        $user = User::create([
            'tenant_id'         => $tenant->id,
            'email'             => $data['email'],
            'password_hash'     => Hash::make($data['password']),
            'full_name'         => $data['full_name'],
            'role'              => 'owner',
            'jlpt_target_level' => $data['jlpt_target_level'] ?? null,
        ]);

        // Send verification email
        $user->sendEmailVerificationNotification();

        // Create token
        // $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            // 'token'   => $token,
            'user'    => $this->userResponse($user),
        ], 201);
    }

    // ── POST /api/v1/auth/login ────────────────────────────
    // public function login(Request $request): JsonResponse
    // {
    //     $data = $request->validate([
    //         'email'    => 'required|email',
    //         'password' => 'required|string',
    //     ]);

    //     $user = User::where('email', $data['email'])->first();

    //     if (!$user || !Hash::check($data['password'], $user->password_hash)) {
    //         return response()->json([
    //             'message' => 'Invalid email or password.',
    //         ], 401);
    //     }

    //     if (!$user->is_active) {
    //         return response()->json([
    //             'message' => 'Your account has been suspended.',
    //         ], 403);
    //     }

    //     // Update last login
    //     $user->update(['last_login_at' => now()]);

    //     // Revoke old tokens and create new one
    //     // $user->tokens()->delete();
    //     // $token = $user->createToken('auth_token')->plainTextToken;


    //     return response()->json([
    //         'message' => 'Login successful.',
    //         // 'token'   => $token,
    //         'user'    => $this->userResponse($user),
    //     ]);
    // }

        public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($data)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account has been suspended.',
            ], 403);
        }

        return response()->json([
            'message' => 'Login successful',
            'user'    => $this->userResponse($user),
        ]);
    }

    // ── POST /api/v1/auth/logout ───────────────────────────
    public function logout(Request $request): JsonResponse
    {
        // $request->user()->currentAccessToken()->delete();
            Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    // ── GET /api/v1/auth/me ────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('tenant.plan');

        return response()->json([
            'user' => $this->userResponse($user),
        ]);
    }

    // ── Private: format user response ─────────────────────
    private function userResponse(User $user): array
    {
        return [
            'id'                 => $user->id,
            'full_name'          => $user->full_name,
            'email'              => $user->email,
            'role'               => $user->role,
            'jlpt_target_level'  => $user->jlpt_target_level,
            'email_verified'     => $user->email_verified,
            'avatar_url'         => $user->avatar_url,
            'tenant'             => [
                'id'          => $user->tenant->id,
                'name'        => $user->tenant->name,
                'tenant_type' => $user->tenant->tenant_type,
                'status'      => $user->tenant->status,
                'plan'        => $user->tenant->plan ? [
                    'id'        => $user->tenant->plan->id,
                    'name'      => $user->tenant->plan->name,
                    'plan_type' => $user->tenant->plan->plan_type,
                    'features'  => $user->tenant->plan->features,
                ] : null,
            ],
        ];
    }
}
