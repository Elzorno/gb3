<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Bonus\BonusService;
use App\Domain\Payout\PayoutService;
use App\Models\Kid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BonusController extends Controller
{
    public function __construct(
        private readonly BonusService $bonus,
        private readonly PayoutService $payout,
    ) {
    }

    public function index(Request $request): View
    {
        $kidId = (int)$request->session()->get('gb2_kid_id', 0);
        $kid = Kid::with('privileges')->find($kidId);
        $week = $this->bonus->weekStart();
        $instances = $this->bonus->listWeek($week);

        // Separate into available, claimed by this kid, and completed
        $available = $instances->filter(fn($i) => $i->status === 'available');
        $myActive = $instances->filter(fn($i) => 
            $i->claimed_by_kid_id === $kidId && in_array($i->status, ['claimed', 'pending', 'rejected'])
        );
        $myCompleted = $instances->filter(fn($i) => 
            $i->claimed_by_kid_id === $kidId && $i->status === 'approved'
        );

        // Get actual bank balances
        $priv = $kid?->privileges;
        $bankCents = $priv->bank_cents ?? 0;
        $bankPhoneMin = $priv->bank_phone_min ?? 0;
        $bankGamesMin = $priv->bank_games_min ?? 0;

        // Get pending payout request, if any
        $pendingPayout = $kidId > 0 ? $this->payout->getPendingRequest($kidId) : null;

        // Check if kid has any bank balance for payout
        $hasPayableBalance = $bankCents > 0 || $bankPhoneMin > 0 || $bankGamesMin > 0;

        return view('app.bonuses', [
            'kid' => $kid,
            'week' => $week,
            'available' => $available,
            'myActive' => $myActive,
            'myCompleted' => $myCompleted,
            'bankCents' => $bankCents,
            'bankPhoneMin' => $bankPhoneMin,
            'bankGamesMin' => $bankGamesMin,
            'pendingPayout' => $pendingPayout,
            'hasPayableBalance' => $hasPayableBalance,
        ]);
    }

    public function claim(Request $request): RedirectResponse
    {
        $kidId = (int)$request->session()->get('gb2_kid_id', 0);
        if ($kidId <= 0) {
            return redirect()->route('app.login')->with('error', 'Please log in first.');
        }

        $v = $request->validate([
            'instance_id' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->bonus->claim((int)$v['instance_id'], $kidId);
            return redirect()->route('app.bonuses')->with('status', 'Bonus claimed! Complete it and submit proof.');
        } catch (\RuntimeException $e) {
            return redirect()->route('app.bonuses')->with('error', 'This bonus is no longer available.');
        }
    }

    public function submit(Request $request): RedirectResponse
    {
        $kidId = (int)$request->session()->get('gb2_kid_id', 0);
        if ($kidId <= 0) {
            return redirect()->route('app.login')->with('error', 'Please log in first.');
        }

        $v = $request->validate([
            'instance_id' => ['required', 'integer', 'min:1'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp,heic,heif', 'max:20480'],
        ], [
            'photo.required' => 'Photo upload failed — the image may be too large. Try taking the photo in normal mode instead of 48MP.',
            'photo.max' => 'Photo is too large (max 20 MB). Try taking the photo in normal mode.',
        ]);

        $file = $request->file('photo');
        $ext = in_array($file->getClientOriginalExtension(), ['jpg','jpeg','png','gif','webp','heic','heif'], true)
            ? $file->getClientOriginalExtension()
            : 'jpg';
        $filename = sprintf(
            'bonus_%s_%d_%d.%s',
            now()->format('Y-m-d'),
            $kidId,
            $v['instance_id'],
            $ext
        );
        $path = $file->storeAs('uploads/proofs', $filename, 'public');

        try {
            $this->bonus->submitProof((int)$v['instance_id'], $kidId, $path);
            return redirect()->route('app.bonuses')->with('status', 'Proof submitted! Waiting for review.');
        } catch (\RuntimeException $e) {
            return redirect()->route('app.bonuses')->with('error', 'Could not submit proof for this bonus.');
        }
    }
}
