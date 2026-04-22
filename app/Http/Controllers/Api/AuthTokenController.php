<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DefaultCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AuthTokenController extends Controller
{
    public function __construct(private DefaultCategoryService $defaultCategoryService)
    {
    }

    public function register(Request $request)
    {
        $supportedCurrencies = array_keys(config('currencies.supported', []));
        $defaultCurrency = config('currencies.default', 'USD');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
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

        return response()->json([
            'token' => $user->createToken('budget-app')->plainTextToken,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        try {
            $valid = $user && Hash::check($data['password'], $user->password);
        } catch (RuntimeException) {
            throw ValidationException::withMessages([
                'email' => ['This account password needs to be reset before it can be used.'],
            ]);
        }

        if (! $valid) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('budget-app')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Token revoked.',
        ]);
    }
}
