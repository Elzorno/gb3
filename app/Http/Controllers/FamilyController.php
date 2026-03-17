<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Kid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FamilyController extends Controller
{
    /**
     * Show all kids with management options.
     */
    public function index(): View
    {
        $kids = Kid::orderBy('sort_order')->get();
        
        // Get privilege status for each kid
        $privileges = DB::table('privileges')
            ->whereIn('kid_id', $kids->pluck('id'))
            ->get()
            ->keyBy('kid_id');

        return view('admin.family.index', [
            'kids' => $kids,
            'privileges' => $privileges,
        ]);
    }

    /**
     * Show form to add a new kid.
     */
    public function create(): View
    {
        return view('admin.family.create');
    }

    /**
     * Store a new kid.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100', 'unique:kids,display_name'],
            'pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ], [
            'pin.size' => 'PIN must be exactly 6 digits.',
            'pin.regex' => 'PIN must contain only numbers.',
        ]);

        // Get max sort order
        $maxSort = Kid::max('sort_order') ?? 0;

        $kid = Kid::create([
            'display_name' => $validated['display_name'],
            'pin_hash' => Hash::make($validated['pin']),
            'sort_order' => $maxSort + 1,
        ]);

        // Create privileges record
        DB::table('privileges')->insert([
            'kid_id' => $kid->id,
            'phone_locked' => 0,
            'games_locked' => 0,
            'other_locked' => 0,
            'bank_phone_min' => 0,
            'bank_games_min' => 0,
            'bank_other_min' => 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.family')
            ->with('success', "{$kid->display_name} has been added to the family!");
    }

    /**
     * Show form to edit a kid.
     */
    public function edit(Kid $kid): View
    {
        $privileges = DB::table('privileges')
            ->where('kid_id', $kid->id)
            ->first();

        return view('admin.family.edit', [
            'kid' => $kid,
            'privileges' => $privileges,
        ]);
    }

    /**
     * Update a kid's details.
     */
    public function update(Request $request, Kid $kid): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kids', 'display_name')->ignore($kid->id),
            ],
        ]);

        $kid->update([
            'display_name' => $validated['display_name'],
        ]);

        return redirect()->route('admin.family')
            ->with('success', "{$kid->display_name}'s profile has been updated.");
    }

    /**
     * Reset a kid's PIN.
     */
    public function resetPin(Request $request, Kid $kid): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'pin_confirmation' => ['required', 'same:pin'],
        ], [
            'pin.size' => 'PIN must be exactly 6 digits.',
            'pin.regex' => 'PIN must contain only numbers.',
            'pin_confirmation.same' => 'PINs do not match.',
        ]);

        $kid->update([
            'pin_hash' => Hash::make($validated['pin']),
        ]);

        return redirect()->route('admin.family.edit', $kid)
            ->with('success', "PIN has been reset for {$kid->display_name}.");
    }

    /**
     * Reorder kids via AJAX.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:kids,id'],
        ]);

        foreach ($validated['order'] as $position => $kidId) {
            Kid::where('id', $kidId)->update(['sort_order' => $position]);
        }

        return redirect()->route('admin.family')
            ->with('success', 'Kid order has been updated.');
    }

    /**
     * Delete a kid (with confirmation).
     */
    public function destroy(Request $request, Kid $kid): RedirectResponse
    {
        $request->validate([
            'confirm_name' => ['required', 'string', Rule::in([$kid->display_name])],
        ], [
            'confirm_name.in' => 'Please type the exact name to confirm deletion.',
        ]);

        $name = $kid->display_name;
        
        // Delete related records first (privileges, etc.)
        DB::table('privileges')->where('kid_id', $kid->id)->delete();
        
        $kid->delete();

        return redirect()->route('admin.family')
            ->with('success', "{$name} has been removed from the family.");
    }
}
