<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Submission\SubmissionService;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        private readonly SubmissionService $service,
    ) {
    }

    public function index(Request $request): View
    {
        $status = (string)$request->query('status', 'pending');
        $kind = (string)$request->query('kind', '');
        $kidId = (int)$request->query('kid_id', 0);
        $perPage = max(5, min(100, (int)$request->query('per_page', 20)));

        $q = Submission::query()
            ->with(['kid', 'slot'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $q->where('status', $status);
        }
        if (in_array($kind, ['base', 'bonus'], true)) {
            $q->where('kind', $kind);
        }
        if ($kidId > 0) {
            $q->where('kid_id', $kidId);
        }

        return view('review.index', [
            'rows' => $q->paginate($perPage)->withQueryString(),
            'status' => $status,
            'kind' => $kind,
            'kidId' => $kidId,
            'perPage' => $perPage,
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
