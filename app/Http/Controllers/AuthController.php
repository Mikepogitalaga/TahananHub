<?php

namespace App\Http\Controllers;

use App\Models\HouseOwner;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->withInput();
        }

        if ($user->status !== 'active') {
            return back()->withErrors(['email' => 'Your account has been suspended or is inactive. Please contact support.'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $this->activityLog->log('login', 'auth', $user->id, $user->name);

        // Honour the intended URL (set when guest clicks "Request to Rent")
        if ($request->input('_intended')) {
            return redirect($request->input('_intended'));
        }

        if ($user->isTenant()) {
            return redirect()->intended(route('houses.index'));
        }

        return $this->redirectByRole($user);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->activityLog->log('logout', 'auth', Auth::id(), Auth::user()?->name ?? 'Unknown');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // -------------------------------------------------------------------------
    // Owner Registration
    // -------------------------------------------------------------------------

    public function showRegisterOwner(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.register-owner');
    }

    public function registerOwner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'business_name'    => ['nullable', 'string', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'role'     => User::ROLE_OWNER,
                'status'   => 'active',
            ]);

            HouseOwner::create([
                'user_id'           => $user->id,
                'business_name'     => $validated['business_name'] ?? null,
                'business_address'  => $validated['business_address'] ?? null,
                'subscription_plan' => 'free',
            ]);

            Auth::login($user);
            $request->session()->regenerate();

            $this->activityLog->log('registered', 'auth', $user->id, $user->name);
        });

        return redirect()->route('owner.dashboard');
    }

    // -------------------------------------------------------------------------
    // Tenant Registration
    // -------------------------------------------------------------------------

    public function showRegisterTenant(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.register-tenant');
    }

    public function registerTenant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => User::ROLE_TENANT,
            'status'   => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->activityLog->log('registered', 'auth', $user->id, $user->name);

        // Return to the house/room they were trying to request
        return redirect()->intended(route('houses.index'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function redirectByRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            User::ROLE_SUPER_ADMIN => redirect()->route('admin.dashboard'),
            User::ROLE_OWNER       => redirect()->route('owner.dashboard'),
            User::ROLE_TENANT      => redirect()->route('tenant.dashboard'),
            default                => redirect()->route('houses.index'),
        };
    }
}
