<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Auth\AdminSessionService;
use App\Domain\Submission\SubmissionService;
use App\Models\Kid;
use App\Models\PayoutRequest;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        private readonly SubmissionService $service,
        private readonly AdminSessionService $adminSession,
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

        $kids = Kid::orderBy('sort_order')->get();
        $pendingCount = Submission::where('status', 'pending')->count();
        $pendingPayoutCount = PayoutRequest::where('status', 'pending')->count();
        $pendingPayouts = PayoutRequest::query()
            ->with('kid')
            ->where('status', 'pending')
            ->orderBy('requested_at')
            ->get();
        $pendingWorkCount = $pendingCount + $pendingPayoutCount;

        return view('admin.reviews.index', [
            'rows' => $q->paginate($perPage)->withQueryString(),
            'status' => $status,
            'kind' => $kind,
            'kidId' => $kidId,
            'perPage' => $perPage,
            'kids' => $kids,
            'pendingCount' => $pendingCount,
            'pendingPayoutCount' => $pendingPayoutCount,
            'pendingPayouts' => $pendingPayouts,
            'pendingWorkCount' => $pendingWorkCount,
        ]);
    }

    public function decide(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'submission_id' => ['required', 'integer', 'min:1'],
            'decision' => ['required', 'in:approved,rejected'],
            'kid_note' => ['nullable', 'string', 'max:400'],
            'admin_note' => ['nullable', 'string', 'max:400'],
        ]);

        $this->service->reviewWithContext(
            (int)$v['submission_id'],
            (string)$v['decision'],
            isset($v['kid_note']) ? (string)$v['kid_note'] : null,
            isset($v['admin_note']) ? (string)$v['admin_note'] : null,
            'admin_session',
            $this->adminSession->actorId($request),
            $this->adminSession->auditKey($request),
        );

        return redirect()->route('admin.reviews')->with('status', 'Review decision saved.');
    }

    public function undo(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'submission_id' => ['required', 'integer', 'min:1'],
        ]);

        $sub = Submission::findOrFail($v['submission_id']);

        // Only allow undo within 5 minutes of the review decision
        if (!$sub->reviewed_at || $sub->reviewed_at->diffInMinutes(now()) > 5) {
            return redirect()->route('admin.reviews')
                ->with('status', 'Undo window has expired (5 minutes).');
        }

        $this->service->undoReview((int)$v['submission_id']);

        return redirect()->route('admin.reviews')
            ->with('status', "Reverted decision for submission #{$sub->id} back to pending.");
    }
}
