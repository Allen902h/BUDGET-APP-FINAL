<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DefaultCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(private DefaultCategoryService $defaultCategoryService)
    {
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $authenticated = Auth::attempt($credentials, $request->boolean('remember'));
        } catch (RuntimeException) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'This account password needs to be reset before it can be used.',
                ]);
        }

        if (! $authenticated) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ]);

        $user = User::where('name', $data['name'])
            ->where('email', $data['email'])
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors([
                    'email' => 'The username and email do not match our records.',
                ]);
        }

        $token = Password::broker()->createToken($user);

        return redirect()->route('password.reset', [
            'token' => $token,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $user = User::where('name', $data['name'])
            ->where('email', $data['email'])
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors([
                    'email' => 'The username and email do not match our records.',
                ]);
        }

        $status = Password::reset(
            [
                'token' => $data['token'],
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $request->input('password_confirmation'),
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __($status),
                ]);
        }

        return redirect()->route('login')->with('success', 'Password reset successful. You can sign in now.');
    }

    public function register(Request $request)
    {
        $supportedCurrencies = array_keys(config('currencies.supported', []));
        $defaultCurrency = config('currencies.default', 'USD');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
            'currency_pref' => ['nullable', 'string', Rule::in($supportedCurrencies)],
            'savings_goal_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'currency_pref' => strtoupper($data['currency_pref'] ?? $defaultCurrency),
            'savings_goal_percentage' => $data['savings_goal_percentage'] ?? 20,
        ]);

        $this->defaultCategoryService->seedFor($user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Your account is ready. Create your first income cycle to begin.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}
