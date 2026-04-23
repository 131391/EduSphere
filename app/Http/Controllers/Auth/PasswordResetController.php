<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password', [
            'school' => app()->bound('currentSchool') ? app('currentSchool') : null,
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $credentials = $this->tenantAwareCredentials($request);

        $status = Password::broker()->sendResetLink($credentials);

        if ($status === Password::RESET_LINK_SENT) {
            return $this->passwordResponse($request, __($status));
        }

        return $this->passwordErrorResponse($request, [
            'email' => [__($status)],
        ]);
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
            'school' => app()->bound('currentSchool') ? app('currentSchool') : null,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::broker()->reset(
            $this->tenantAwareCredentials($request, ['email', 'password', 'password_confirmation', 'token']),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __($status),
                    'redirect' => route('login'),
                ]);
            }

            return redirect()->route('login')->with('success', __($status));
        }

        return $this->passwordErrorResponse($request, [
            'email' => [__($status)],
        ]);
    }

    protected function tenantAwareCredentials(Request $request, array $fields = ['email']): array
    {
        $credentials = $request->only($fields);

        if (app()->bound('currentSchool')) {
            $user = User::withoutGlobalScopes()
                ->where('email', $request->input('email'))
                ->where('school_id', app('currentSchool')->id)
                ->first();

            $credentials['email'] = $user?->email ?? $request->input('email');
        }

        return $credentials;
    }

    protected function passwordResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    protected function passwordErrorResponse(Request $request, array $errors)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'We could not process your request.',
                'errors' => $errors,
            ], 422);
        }

        return back()->withErrors($errors);
    }
}
