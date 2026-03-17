<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChoreSlot;
use App\Models\Kid;
use App\Models\RotationRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RotationController extends Controller
{
    /**
     * Show rotation management page.
     */
    public function index(): View
    {
        $slots = ChoreSlot::orderBy('sort_order')->get();
        $kids = Kid::orderBy('sort_order')->get();
        $rule = RotationRule::first();

        // Calculate today's rotation preview
        $today = now();
        $preview = $this->calculateRotationPreview($rule, $kids, $slots, $today);

        return view('admin.rotation.index', [
            'slots' => $slots,
            'kids' => $kids,
            'rule' => $rule,
            'preview' => $preview,
            'today' => $today,
        ]);
    }

    /**
     * Show form to create a new chore slot.
     */
    public function createSlot(): View
    {
        return view('admin.rotation.create-slot');
    }

    /**
     * Store a new chore slot.
     */
    public function storeSlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'active' => ['boolean'],
        ]);

        $maxSort = ChoreSlot::max('sort_order') ?? 0;

        ChoreSlot::create([
            'title' => $validated['title'],
            'active' => $validated['active'] ?? true,
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()->route('admin.rotation')
            ->with('success', 'Chore slot has been added.');
    }

    /**
     * Show form to edit a chore slot.
     */
    public function editSlot(ChoreSlot $slot): View
    {
        return view('admin.rotation.edit-slot', [
            'slot' => $slot,
        ]);
    }

    /**
     * Update a chore slot.
     */
    public function updateSlot(Request $request, ChoreSlot $slot): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'active' => ['boolean'],
        ]);

        $slot->update([
            'title' => $validated['title'],
            'active' => $validated['active'] ?? false,
        ]);

        return redirect()->route('admin.rotation')
            ->with('success', 'Chore slot has been updated.');
    }

    /**
     * Toggle a chore slot's active status.
     */
    public function toggleSlot(ChoreSlot $slot): RedirectResponse
    {
        $slot->update(['active' => !$slot->active]);
        
        $status = $slot->active ? 'activated' : 'deactivated';
        return redirect()->route('admin.rotation')
            ->with('success', "{$slot->title} has been {$status}.");
    }

    /**
     * Reorder chore slots.
     */
    public function reorderSlots(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:chore_slots,id'],
        ]);

        foreach ($validated['order'] as $position => $slotId) {
            ChoreSlot::where('id', $slotId)->update(['sort_order' => $position]);
        }

        return redirect()->route('admin.rotation')
            ->with('success', 'Chore order has been updated.');
    }

    /**
     * Delete a chore slot.
     */
    public function destroySlot(ChoreSlot $slot): RedirectResponse
    {
        $title = $slot->title;
        $slot->delete();

        return redirect()->route('admin.rotation')
            ->with('success', "{$title} has been deleted.");
    }

    /**
     * Update rotation rule configuration.
     */
    public function updateRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kids' => ['required', 'array', 'min:1'],
            'kids.*' => ['integer', 'exists:kids,id'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*' => ['integer', 'exists:chore_slots,id'],
            'anchor_monday' => ['required', 'date'],
        ]);

        $rule = RotationRule::first();

        if ($rule) {
            $rule->update([
                'kids_json' => json_encode($validated['kids']),
                'slots_json' => json_encode($validated['slots']),
                'anchor_monday' => $validated['anchor_monday'],
            ]);
        } else {
            RotationRule::create([
                'name' => 'default',
                'kids_json' => json_encode($validated['kids']),
                'slots_json' => json_encode($validated['slots']),
                'anchor_monday' => $validated['anchor_monday'],
            ]);
        }

        return redirect()->route('admin.rotation')
            ->with('success', 'Rotation rule has been updated.');
    }

    /**
     * Calculate rotation preview for the week.
     */
    private function calculateRotationPreview(?RotationRule $rule, $kids, $slots, $today): array
    {
        if (!$rule) {
            return [];
        }

        $kidValues = json_decode($rule->kids_json, true) ?: [];
        $slotValues = json_decode($rule->slots_json, true) ?: [];
        
        if (empty($kidValues) || empty($slotValues)) {
            return [];
        }
        
        // Handle legacy format (names/titles) vs new format (IDs)
        $firstKid = $kidValues[0] ?? null;
        $legacyKidFormat = $firstKid && !is_numeric($firstKid);
        
        $firstSlot = $slotValues[0] ?? null;
        $legacySlotFormat = $firstSlot && !is_numeric($firstSlot);
        
        // Map to actual kids and slots
        $rotationKids = collect();
        foreach ($kidValues as $val) {
            if ($legacyKidFormat) {
                $kid = $kids->firstWhere('display_name', $val);
            } else {
                $kid = $kids->firstWhere('id', $val);
            }
            if ($kid) {
                $rotationKids->push($kid);
            }
        }
        
        $rotationSlots = collect();
        foreach ($slotValues as $val) {
            if ($legacySlotFormat) {
                $slot = $slots->firstWhere('title', $val);
            } else {
                $slot = $slots->firstWhere('id', $val);
            }
            if ($slot && $slot->active) {
                $rotationSlots->push($slot);
            }
        }
        
        if ($rotationKids->isEmpty() || $rotationSlots->isEmpty()) {
            return [];
        }

        $currentMonday = $today->copy()->startOfWeek();

        $preview = [];
        
        // Generate preview for Monday through Friday
        for ($dayOffset = 0; $dayOffset < 5; $dayOffset++) {
            $day = $currentMonday->copy()->addDays($dayOffset);
            $dayName = $day->format('l');
            
            // Offset is simply day-of-week: Mon=0, Tue=1, Wed=2, Thu=3, Fri=4
            $dayAssignments = [];
            foreach ($rotationKids as $kidIndex => $kid) {
                $slotIndex = ($kidIndex + $dayOffset) % $rotationSlots->count();
                $slot = $rotationSlots[$slotIndex];
                
                $dayAssignments[] = [
                    'kid' => $kid,
                    'slot' => $slot,
                ];
            }
            
            $preview[] = [
                'date' => $day,
                'dayName' => $dayName,
                'isToday' => $day->isSameDay($today),
                'assignments' => $dayAssignments,
            ];
        }

        return $preview;
    }
}
