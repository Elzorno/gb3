<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BonusDef;
use App\Models\InfractionDef;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DefinitionController extends Controller
{
    /**
     * Display all definitions (bonuses and infractions)
     */
    public function index(): View
    {
        $bonuses = BonusDef::orderBy('sort_order')->get();
        $infractions = InfractionDef::orderBy('sort_order')->get();

        return view('admin.definitions.index', [
            'bonuses' => $bonuses,
            'infractions' => $infractions,
        ]);
    }

    // =========================================================================
    // Bonus Definitions
    // =========================================================================

    /**
     * Show form to create a new bonus definition
     */
    public function createBonus(): View
    {
        return view('admin.definitions.bonus-create');
    }

    /**
     * Store a new bonus definition
     */
    public function storeBonus(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'reward_cents' => ['nullable', 'integer', 'min:0'],
            'reward_phone_min' => ['nullable', 'integer', 'min:0'],
            'reward_games_min' => ['nullable', 'integer', 'min:0'],
            'max_per_week' => ['nullable', 'integer', 'min:1', 'max:10'],
            'active' => ['nullable'],
        ]);

        $maxOrder = BonusDef::max('sort_order') ?? 0;

        BonusDef::create([
            'title' => $v['title'],
            'reward_cents' => $v['reward_cents'] ?? 0,
            'reward_phone_min' => $v['reward_phone_min'] ?? 0,
            'reward_games_min' => $v['reward_games_min'] ?? 0,
            'max_per_week' => $v['max_per_week'] ?? 1,
            'active' => isset($v['active']),
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.definitions')->with('status', 'Bonus definition created.');
    }

    /**
     * Show form to edit a bonus definition
     */
    public function editBonus(BonusDef $bonus): View
    {
        return view('admin.definitions.bonus-edit', [
            'bonus' => $bonus,
        ]);
    }

    /**
     * Update a bonus definition
     */
    public function updateBonus(Request $request, BonusDef $bonus): RedirectResponse
    {
        $v = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'reward_cents' => ['nullable', 'integer', 'min:0'],
            'reward_phone_min' => ['nullable', 'integer', 'min:0'],
            'reward_games_min' => ['nullable', 'integer', 'min:0'],
            'max_per_week' => ['nullable', 'integer', 'min:1', 'max:10'],
            'active' => ['nullable'],
        ]);

        $bonus->update([
            'title' => $v['title'],
            'reward_cents' => $v['reward_cents'] ?? 0,
            'reward_phone_min' => $v['reward_phone_min'] ?? 0,
            'reward_games_min' => $v['reward_games_min'] ?? 0,
            'max_per_week' => $v['max_per_week'] ?? 1,
            'active' => isset($v['active']),
        ]);

        return redirect()->route('admin.definitions')->with('status', 'Bonus definition updated.');
    }

    /**
     * Toggle bonus active status
     */
    public function toggleBonus(BonusDef $bonus): RedirectResponse
    {
        $bonus->active = !$bonus->active;
        $bonus->save();

        $status = $bonus->active ? 'enabled' : 'disabled';
        return redirect()->back()->with('status', "Bonus \"{$bonus->title}\" {$status}.");
    }

    /**
     * Reorder bonus definitions
     */
    public function reorderBonuses(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'min:1'],
        ]);

        foreach ($v['order'] as $index => $id) {
            BonusDef::where('id', $id)->update(['sort_order' => $index]);
        }

        return redirect()->back()->with('status', 'Bonus order updated.');
    }

    /**
     * Delete a bonus definition
     */
    public function destroyBonus(BonusDef $bonus): RedirectResponse
    {
        $title = $bonus->title;
        $bonus->delete();

        return redirect()->route('admin.definitions')->with('status', "Bonus \"{$title}\" deleted.");
    }

    // =========================================================================
    // Infraction Definitions
    // =========================================================================

    /**
     * Show form to create a new infraction definition
     */
    public function createInfraction(): View
    {
        return view('admin.definitions.infraction-create');
    }

    /**
     * Store a new infraction definition
     */
    public function storeInfraction(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z_]+$/'],
            'label' => ['required', 'string', 'max:100'],
            'mode' => ['required', 'in:add,set'],
            'days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'active' => ['nullable'],
        ]);

        $maxOrder = InfractionDef::max('sort_order') ?? 0;

        InfractionDef::create([
            'code' => strtoupper($v['code']),
            'label' => $v['label'],
            'mode' => $v['mode'],
            'days' => $v['days'] ?? 0,
            'active' => isset($v['active']),
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.definitions')->with('status', 'Infraction definition created.');
    }

    /**
     * Show form to edit an infraction definition
     */
    public function editInfraction(InfractionDef $infraction): View
    {
        return view('admin.definitions.infraction-edit', [
            'infraction' => $infraction,
        ]);
    }

    /**
     * Update an infraction definition
     */
    public function updateInfraction(Request $request, InfractionDef $infraction): RedirectResponse
    {
        $v = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z_]+$/'],
            'label' => ['required', 'string', 'max:100'],
            'mode' => ['required', 'in:add,set'],
            'days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'active' => ['nullable'],
        ]);

        $infraction->update([
            'code' => strtoupper($v['code']),
            'label' => $v['label'],
            'mode' => $v['mode'],
            'days' => $v['days'] ?? 0,
            'active' => isset($v['active']),
        ]);

        return redirect()->route('admin.definitions')->with('status', 'Infraction definition updated.');
    }

    /**
     * Toggle infraction active status
     */
    public function toggleInfraction(InfractionDef $infraction): RedirectResponse
    {
        $infraction->active = !$infraction->active;
        $infraction->save();

        $status = $infraction->active ? 'enabled' : 'disabled';
        return redirect()->back()->with('status', "Infraction \"{$infraction->label}\" {$status}.");
    }

    /**
     * Reorder infraction definitions
     */
    public function reorderInfractions(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'min:1'],
        ]);

        foreach ($v['order'] as $index => $id) {
            InfractionDef::where('id', $id)->update(['sort_order' => $index]);
        }

        return redirect()->back()->with('status', 'Infraction order updated.');
    }

    /**
     * Delete an infraction definition
     */
    public function destroyInfraction(InfractionDef $infraction): RedirectResponse
    {
        $label = $infraction->label;
        $infraction->delete();

        return redirect()->route('admin.definitions')->with('status', "Infraction \"{$label}\" deleted.");
    }
}
