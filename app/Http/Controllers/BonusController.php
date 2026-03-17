<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Bonus\BonusService;
use App\Models\Kid;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BonusController extends Controller
{
    public function __construct(
        private readonly BonusService $bonus,
    ) {
    }

    public function index(Request $request): View
    {
        $kidId = (int)$request->session()->get('gb2_kid_id', 0);
        $kid = Kid::find($kidId);
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

        // Calculate earnings for this week
        $weekEarnings = Submission::where('kid_id', $kidId)
            ->where('kind', 'bonus')
            ->where('week_start', $week)
            ->where('status', 'approved')
            ->with('bonusInstance.definition')
            ->get()
            ->sum(fn($s) => $s->bonusInstance?->definition?->reward_cents ?? 0);

        return view('app.bonuses', [
            'kid' => $kid,
            'week' => $week,
            'available' => $available,
            'myActive' => $myActive,
            'myCompleted' => $myCompleted,
            'weekEarnings' => $weekEarnings,
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
            'proof_path' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->bonus->submitProof((int)$v['instance_id'], $kidId, (string)$v['proof_path']);
            return redirect()->route('app.bonuses')->with('status', 'Proof submitted! Waiting for review.');
        } catch (\RuntimeException $e) {
            return redirect()->route('app.bonuses')->with('error', 'Could not submit proof for this bonus.');
        }
    }
}
