<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Payout\PayoutService;
use App\Models\Kid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function __construct(
        private readonly PayoutService $payout,
    ) {
    }

    /**
     * Kid: Request a payout of their current bank balance.
     */
    public function request(Request $request): RedirectResponse
    {
        $kidId = (int) $request->session()->get('gb2_kid_id', 0);
        if ($kidId <= 0) {
            return redirect()->route('app.login')->with('error', 'Please log in first.');
        }

        try {
            $this->payout->requestPayout($kidId);
            return redirect()->route('app.bonuses')
                ->with('status', 'Payout requested! Waiting for review.');
        } catch (\RuntimeException $e) {
            return redirect()->route('app.bonuses')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: List pending payout requests.
     */
    public function adminIndex(Request $request): View
    {
        $pendingPayouts = $this->payout->listPending();
        $kids = Kid::orderBy('sort_order')->get();

        return view('admin.payouts.index', [
            'payouts' => $pendingPayouts,
            'kids' => $kids,
        ]);
    }

    /**
     * Admin: Approve or deny a payout request.
     */
    public function adminDecide(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'payout_id' => ['required', 'integer', 'min:1'],
            'decision' => ['required', 'in:approved,denied'],
            'note' => ['nullable', 'string', 'max:400'],
        ]);

        $payoutId = (int) $v['payout_id'];
        $decision = (string) $v['decision'];
        $note = isset($v['note']) ? (string) $v['note'] : null;

        try {
            if ($decision === 'approved') {
                $this->payout->approve($payoutId, $note, 'admin', 1);
                $message = 'Payout approved and bank deducted.';
            } else {
                $this->payout->deny($payoutId, $note, 'admin', 1);
                $message = 'Payout request denied.';
            }

            return redirect()->route('admin.payouts')
                ->with('status', $message);
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.payouts')
                ->with('error', $e->getMessage());
        }
    }
}
