<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Submission\SubmissionService;
use App\Models\Kid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function __construct(
        private readonly SubmissionService $service,
    ) {
    }

    public function create(Request $request): View
    {
        $kidId = (int)($request->session()->get('gb2_kid_id', 0));
        $kid = $kidId > 0 ? Kid::query()->find($kidId) : null;

        return view('submission.create', [
            'kid' => $kid,
        ]);
    }

    public function storeBase(Request $request): RedirectResponse
    {
        $kidId = (int)($request->session()->get('gb2_kid_id', 0));
        if ($kidId <= 0) {
            return redirect()->route('kid.login')->with('status', 'Please log in first.');
        }

        $v = $request->validate([
            'slot_id' => ['required', 'integer', 'min:1'],
            'day' => ['required', 'date_format:Y-m-d'],
            'proof_path' => ['required', 'string', 'max:255'],
        ]);

        $this->service->submitBase(
            $kidId,
            (int)$v['slot_id'],
            (string)$v['day'],
            (string)$v['proof_path'],
        );

        return redirect()->route('submission.create')->with('status', 'Submission sent for review.');
    }
}
