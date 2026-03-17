<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Infraction\InfractionService;
use App\Models\InfractionEvent;
use App\Models\Kid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfractionController extends Controller
{
    public function __construct(
        private readonly InfractionService $infractions,
    ) {
    }

    public function index(Request $request): View
    {
        return view('admin.infractions.index', [
            'kids' => Kid::query()->orderBy('sort_order')->orderBy('id')->get(),
            'defs' => $this->infractions->activeDefinitions(),
            'events' => InfractionEvent::query()
                ->with(['kid', 'definition'])
                ->orderByDesc('ts')
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
        ]);
    }

    public function apply(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'kid_id' => ['required', 'integer', 'min:1'],
            'infraction_def_id' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:300'],
            'lane' => ['nullable', 'string', 'in:ordinary,safety'],
            'repair' => ['nullable', 'string', 'in:redo_task,apology,calm_recheck,review_tomorrow,none'],
        ]);

        $kid = Kid::findOrFail($v['kid_id']);
        $def = $this->infractions->activeDefinitions()->firstWhere('id', $v['infraction_def_id']);

        $note = trim($v['note'] ?? '');
        if (!empty($v['lane']) && $v['lane'] === 'safety') {
            $note = '[SAFETY] ' . $note;
        }
        if (!empty($v['repair']) && $v['repair'] !== 'none') {
            $repairLabels = [
                'redo_task' => 'Redo the task correctly',
                'apology' => 'Genuine apology or repair action',
                'calm_recheck' => 'Calm recheck later today',
                'review_tomorrow' => 'Review at next check-in',
            ];
            $note .= ' [Repair: ' . ($repairLabels[$v['repair']] ?? $v['repair']) . ']';
        }

        $this->infractions->apply(
            (int)$v['kid_id'],
            (int)$v['infraction_def_id'],
            $note,
            'admin',
            0,
        );

        return redirect()->route('admin.infractions')
            ->with('success', "Applied \"{$def->label}\" consequence to {$kid->display_name}.");
    }
}
