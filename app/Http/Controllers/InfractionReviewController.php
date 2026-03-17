<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Infraction\InfractionService;
use App\Models\InfractionEvent;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfractionReviewController extends Controller
{
    public function __construct(
        private readonly InfractionService $infractions,
    ) {
    }

    public function index(): View
    {
        $today = CarbonImmutable::now(config('app.timezone'))->format('Y-m-d');
        $nextWeek = CarbonImmutable::now(config('app.timezone'))->addDays(7)->format('Y-m-d');

        return view('infraction.review', [
            'dueNow' => InfractionEvent::query()
                ->with(['kid', 'definition'])
                ->whereNotNull('review_on')
                ->whereDate('review_on', '<=', $today)
                ->whereNull('reviewed_at')
                ->orderBy('review_on')
                ->orderByDesc('ts')
                ->get(),
            'upcoming' => InfractionEvent::query()
                ->with(['kid', 'definition'])
                ->whereNotNull('review_on')
                ->whereDate('review_on', '>', $today)
                ->whereDate('review_on', '<=', $nextWeek)
                ->whereNull('reviewed_at')
                ->orderBy('review_on')
                ->orderByDesc('ts')
                ->get(),
        ]);
    }

    public function decide(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'event_id' => ['required', 'integer', 'min:1'],
            'action' => ['required', 'in:review_only,unlock,shorten'],
            'keep_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'reset_strike' => ['nullable', 'boolean'],
            'review_note' => ['nullable', 'string', 'max:400'],
        ]);

        $this->infractions->reviewEvent(
            (int)$v['event_id'],
            (string)$v['action'],
            (int)($v['keep_minutes'] ?? 0),
            isset($v['review_note']) ? (string)$v['review_note'] : null,
            (bool)($v['reset_strike'] ?? false),
            'admin',
            0,
        );

        return redirect()->route('admin.infractions.review')->with('status', 'Infraction review saved.');
    }
}
