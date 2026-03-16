<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Bonus\BonusService;
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
        $week = $this->bonus->weekStart();

        return view('bonus.index', [
            'week' => $week,
            'kidId' => (int)$request->session()->get('gb2_kid_id', 0),
            'instances' => $this->bonus->listWeek($week),
        ]);
    }

    public function claim(Request $request): RedirectResponse
    {
        $kidId = (int)$request->session()->get('gb2_kid_id', 0);
        if ($kidId <= 0) {
            return redirect()->route('kid.login')->with('status', 'Please log in first.');
        }

        $v = $request->validate([
            'instance_id' => ['required', 'integer', 'min:1'],
        ]);

        $this->bonus->claim((int)$v['instance_id'], $kidId);
        return redirect()->route('bonus.index')->with('status', 'Bonus claimed.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $kidId = (int)$request->session()->get('gb2_kid_id', 0);
        if ($kidId <= 0) {
            return redirect()->route('kid.login')->with('status', 'Please log in first.');
        }

        $v = $request->validate([
            'instance_id' => ['required', 'integer', 'min:1'],
            'proof_path' => ['required', 'string', 'max:255'],
        ]);

        $this->bonus->submitProof((int)$v['instance_id'], $kidId, (string)$v['proof_path']);
        return redirect()->route('bonus.index')->with('status', 'Bonus proof sent for review.');
    }
}
