<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Submission\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        private readonly SubmissionService $service,
    ) {
    }

    public function index(): View
    {
        return view('review.index', [
            'pending' => $this->service->pendingList(100),
        ]);
    }

    public function decide(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'submission_id' => ['required', 'integer', 'min:1'],
            'decision' => ['required', 'in:approved,rejected'],
            'note' => ['nullable', 'string', 'max:400'],
        ]);

        $this->service->review(
            (int)$v['submission_id'],
            (string)$v['decision'],
            isset($v['note']) ? (string)$v['note'] : null,
        );

        return redirect()->route('review.index')->with('status', 'Review decision saved.');
    }
}
