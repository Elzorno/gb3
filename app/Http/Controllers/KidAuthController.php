<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Auth\KidSessionService;
use App\Domain\Auth\PinRateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KidAuthController extends Controller
{
    public function __construct(
        private readonly KidSessionService $kidSessions,
    ) {
    }

    public function showLogin(Request $request): View
    {
        return view('auth.kid-login', [
            'kidId' => (string)$request->old('kid_id', ''),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kid_id' => ['required', 'integer', 'min:1'],
            'pin' => ['required', 'string'],
        ]);

        $kidId = (int)$validated['kid_id'];
        $pin = (string)$validated['pin'];

        if (!$this->kidSessions->pinPolicyOk($pin, 6, 6)) {
            return back()->withInput()->withErrors(['pin' => 'PIN must be exactly 6 digits.']);
        }

        $limiter = new PinRateLimiter($request->session(), 300, 5, 600);
        $lockKey = $this->kidSessions->lockKey($request, $kidId);
        $remaining = $limiter->remaining($lockKey);

        if ($remaining > 0) {
            $wait = $this->kidSessions->formatWait($remaining);
            return back()->withInput()->withErrors(['pin' => 'Too many attempts. Please wait ' . $wait . '.']);
        }

        // Temporary scaffold auth path.
        // Replace with DB-backed kid auth in module implementation phase.
        $stubPin = (string)env('GB2_AUTH_STUB_PIN', '123456');
        if ($pin !== $stubPin) {
            $after = $limiter->recordFailure($lockKey);
            if ($after > 0) {
                $wait = $this->kidSessions->formatWait($after);
                return back()->withInput()->withErrors(['pin' => 'Too many attempts. Please wait ' . $wait . '.']);
            }

            return back()->withInput()->withErrors(['pin' => 'PIN did not match.']);
        }

        $limiter->clear($lockKey);
        $this->kidSessions->loginKid($request, $kidId);

        return redirect()->route('rewrite.home')->with('status', 'Kid session started.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->kidSessions->logoutKid($request);

        return redirect()->route('kid.login')->with('status', 'Logged out.');
    }
}
