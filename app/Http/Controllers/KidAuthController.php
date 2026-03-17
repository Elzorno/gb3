<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Auth\KidSessionService;
use App\Domain\Auth\PinRateLimiter;
use App\Models\Kid;
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

        $kid = Kid::query()->find($kidId);
        if ($kid === null) {
            return back()->withInput()->withErrors(['kid_id' => 'Kid account was not found.']);
        }

        $hash = (string)($kid->pin_hash ?? '');
        if ($hash === '') {
            return back()->withInput()->withErrors(['pin' => 'PIN is not set for this kid yet.']);
        }

        if (!password_verify($pin, $hash)) {
            $after = $limiter->recordFailure($lockKey);
            if ($after > 0) {
                $wait = $this->kidSessions->formatWait($after);
                return back()->withInput()->withErrors(['pin' => 'Too many attempts. Please wait ' . $wait . '.']);
            }

            return back()->withInput()->withErrors(['pin' => 'PIN did not match.']);
        }

        $limiter->clear($lockKey);
        $this->kidSessions->loginKid($request, (int)$kid->id);

        return redirect()->route('app.today')->with('status', 'Welcome back, ' . $kid->name . '!');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->kidSessions->logoutKid($request);

        return redirect()->route('app.login')->with('status', 'See you later!');
    }
}
