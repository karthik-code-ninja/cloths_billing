<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ManagerOverride;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('pos');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            AuditLog::log('user_login', Auth::user());

            return redirect()->intended(route('pos'));
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::log('user_logout', Auth::user());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Verify manager credentials for cashier overrides (discounts, refunds, overrides).
     */
    public function verifyManagerOverride(Request $request)
    {
        $request->validate([
            'manager_email' => ['required', 'email'],
            'manager_password' => ['required', 'string'],
            'action_type' => ['required', 'string'],
            'threshold_value' => ['nullable', 'numeric'],
            'actual_value' => ['nullable', 'numeric'],
            'reason' => ['nullable', 'string'],
        ]);

        $manager = User::where('email', $request->manager_email)->first();

        if (! $manager || ! Hash::check($request->manager_password, $manager->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid manager credentials.',
            ], 422);
        }

        if (! $manager->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have manager authorization privileges.',
            ], 403);
        }

        $override = ManagerOverride::create([
            'manager_id' => $manager->id,
            'cashier_id' => Auth::id() ?: $manager->id,
            'action_type' => $request->action_type,
            'threshold_value' => $request->threshold_value,
            'actual_value' => $request->actual_value,
            'reason' => $request->reason ?: 'Manager manual authorization',
            'status' => 'approved',
        ]);

        AuditLog::log('manager_override_approved', $override, null, [
            'manager' => $manager->name,
            'action' => $request->action_type,
            'value' => $request->actual_value,
        ]);

        return response()->json([
            'success' => true,
            'override_id' => $override->id,
            'manager_name' => $manager->name,
            'message' => 'Override approved successfully.',
        ]);
    }
}
