<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Kid;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KidAuth
{
    /**
     * Handle an incoming request.
     * Ensures a kid is logged in, redirects to kid login otherwise.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $kidId = $request->session()->get('gb2_kid_id');

        if (!$kidId) {
            return redirect()->route('app.login');
        }

        // Verify the kid still exists
        $kid = Kid::find($kidId);
        if (!$kid) {
            $request->session()->forget(['gb2_kid_id', 'gb2_kid_logged_in_at']);
            return redirect()->route('app.login')
                ->with('error', 'Your account was not found. Please log in again.');
        }

        // Make kid available to all views
        view()->share('currentKid', $kid);
        
        // Also add kid name to session for easy access in layouts
        $request->session()->put('kid_name', $kid->name);

        return $next($request);
    }
}
