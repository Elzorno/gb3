<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Auth\AdminSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function __construct(
        private readonly AdminSessionService $adminSession
    ) {
    }

    /**
     * Handle an incoming request.
     * Ensures admin is logged in, redirects to login otherwise.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if setup is needed first
        if ($this->adminSession->needsSetup()) {
            return redirect()->route('admin.setup');
        }

        // Check if admin is logged in
        if (!$this->adminSession->isLoggedIn($request)) {
            // Store intended URL for redirect after login
            if ($request->isMethod('GET')) {
                session()->put('admin_intended_url', $request->fullUrl());
            }
            
            return redirect()->route('admin.login');
        }

        // Share family name with all admin views
        $familyName = DB::table('settings')->where('key', 'family_name')->value('value') ?? 'Family';
        view()->share('familyName', $familyName);

        return $next($request);
    }
}
