<?php

declare(strict_types=1);

namespace App\Domain\Rotation;

use App\Models\Assignment;
use App\Models\ChoreSlot;
use App\Models\Kid;
use App\Models\RotationRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class RotationPlanner
{
    public function isWeekday(CarbonImmutable $date): bool
    {
        $n = (int)$date->format('N');
        return $n >= 1 && $n <= 5;
    }

    public function ensureAssignmentsForDay(CarbonImmutable $date): Collection
    {
        if (!$this->isWeekday($date)) {
            return collect();
        }

        $day = $date->format('Y-m-d');
        $existing = Assignment::query()
            ->with(['kid', 'slot'])
            ->whereDate('day', $day)
            ->orderBy('kid_id')
            ->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $rule = RotationRule::query()->orderBy('id')->first();
        if ($rule === null) {
            return collect();
        }

        $kidsByName = Kid::query()->orderBy('sort_order')->orderBy('id')->get()->keyBy('display_name');
        $kidsById = Kid::query()->orderBy('sort_order')->orderBy('id')->get()->keyBy('id');
        $slotsByTitle = ChoreSlot::query()->where('active', true)->orderBy('sort_order')->orderBy('id')->get()->keyBy('title');
        $slotsById = ChoreSlot::query()->where('active', true)->orderBy('sort_order')->orderBy('id')->get()->keyBy('id');

        $kidValues = json_decode((string)$rule->kids_json, true);
        $slotValues = json_decode((string)$rule->slots_json, true);
        if (!is_array($kidValues) || !is_array($slotValues) || count($kidValues) === 0 || count($slotValues) === 0) {
            return collect();
        }
        
        // Detect format: legacy uses names/titles, new uses IDs
        $firstKid = $kidValues[0] ?? null;
        $legacyKidFormat = $firstKid && !is_numeric($firstKid);
        
        $firstSlot = $slotValues[0] ?? null;
        $legacySlotFormat = $firstSlot && !is_numeric($firstSlot);
        
        // Build ordered lists of kids and slots
        $orderedKids = [];
        foreach ($kidValues as $val) {
            if ($legacyKidFormat) {
                $kid = $kidsByName->get((string)$val);
            } else {
                $kid = $kidsById->get((int)$val);
            }
            if ($kid) {
                $orderedKids[] = $kid;
            }
        }
        
        $orderedSlots = [];
        foreach ($slotValues as $val) {
            if ($legacySlotFormat) {
                $slot = $slotsByTitle->get((string)$val);
            } else {
                $slot = $slotsById->get((int)$val);
            }
            if ($slot) {
                $orderedSlots[] = $slot;
            }
        }
        
        if (count($orderedKids) === 0 || count($orderedSlots) === 0) {
            return collect();
        }

        // Offset is simply day-of-week: Mon=0, Tue=1, Wed=2, Thu=3, Fri=4
        // Each kid shifts to the next chore each day
        $dayOfWeekOffset = (int)$date->format('N') - 1; // N: 1=Mon, 7=Sun

        $rotation = [];
        foreach ($orderedKids as $idx => $kid) {
            $slotIdx = ($idx + $dayOfWeekOffset) % count($orderedSlots);
            $slot = $orderedSlots[$slotIdx];

            $rotation[] = [
                'day' => $day,
                'kid_id' => (int)$kid->id,
                'slot_id' => (int)$slot->id,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rotation) {
            Assignment::query()->insert($rotation);
        }

        return Assignment::query()
            ->with(['kid', 'slot'])
            ->whereDate('day', $day)
            ->orderBy('kid_id')
            ->get();
    }
}
