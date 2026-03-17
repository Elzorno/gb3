<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Auth\AdminSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function __construct(
        private readonly AdminSessionService $adminSession
    ) {
    }

    /**
     * Show admin login form.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        // If already logged in, redirect to dashboard
        if ($this->adminSession->isLoggedIn($request)) {
            return redirect()->route('admin.dashboard');
        }

        // If setup needed, redirect there
        if ($this->adminSession->needsSetup()) {
            return redirect()->route('admin.setup');
        }

        return view('auth.admin-login');
    }

    /**
     * Process admin login.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Rate limiting - 5 attempts per minute per IP
        $key = $this->adminSession->rateLimitKey($request);
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'password' => "Too many login attempts. Please wait {$seconds} seconds.",
            ]);
        }

        $storedHash = $this->adminSession->getStoredPasswordHash();
        
        if ($storedHash === null) {
            return redirect()->route('admin.setup');
        }

        if (!$this->adminSession->verifyPassword($validated['password'], $storedHash)) {
            RateLimiter::hit($key, 60);
            
            return back()->withErrors([
                'password' => 'Invalid password.',
            ]);
        }

        // Clear rate limiter on success
        RateLimiter::clear($key);

        // Log in
        $this->adminSession->login($request);

        // Redirect to intended URL or dashboard
        $intended = session()->pull('admin_intended_url', route('admin.dashboard'));
        
        return redirect($intended)->with('success', 'Welcome back!');
    }

    /**
     * Log out admin.
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->adminSession->logout($request);

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out.');
    }

    /**
     * Show initial setup form (set admin password).
     */
    public function showSetup(Request $request): View|RedirectResponse
    {
        // If password already set, redirect to login
        if (!$this->adminSession->needsSetup()) {
            return redirect()->route('admin.login');
        }

        return view('auth.admin-setup');
    }

    /**
     * Process initial setup (set admin password).
     */
    public function setup(Request $request): RedirectResponse
    {
        // If password already set, don't allow re-setup
        if (!$this->adminSession->needsSetup()) {
            return redirect()->route('admin.login')
                ->with('error', 'Setup has already been completed.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'family_name' => ['required', 'string', 'max:100'],
        ]);

        // Set the admin password
        $this->adminSession->setPassword($validated['password']);

        // Set family name in settings
        \DB::table('settings')->updateOrInsert(
            ['key' => 'family_name'],
            ['value' => $validated['family_name'], 'updated_at' => now()]
        );

        // Log the admin in
        $this->adminSession->login($request);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Setup complete! Welcome to Grounding Buddy.');
    }
}
