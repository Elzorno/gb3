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
        ]);

        $kid = Kid::findOrFail($v['kid_id']);
        $def = $this->infractions->activeDefinitions()->firstWhere('id', $v['infraction_def_id']);
        
        $this->infractions->apply(
            (int)$v['kid_id'],
            (int)$v['infraction_def_id'],
            isset($v['note']) ? (string)$v['note'] : '',
            'admin',
            0,
        );

        return redirect()->route('admin.infractions')
            ->with('success', "Applied \"{$def->label}\" consequence to {$kid->display_name}.");
    }
}
