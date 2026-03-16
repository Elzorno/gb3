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
        $slotsByTitle = ChoreSlot::query()->where('active', true)->orderBy('sort_order')->orderBy('id')->get()->keyBy('title');

        $kidNames = json_decode((string)$rule->kids_json, true);
        $slotTitles = json_decode((string)$rule->slots_json, true);
        if (!is_array($kidNames) || !is_array($slotTitles) || count($kidNames) === 0 || count($slotTitles) === 0) {
            return collect();
        }

        $anchor = CarbonImmutable::parse((string)$rule->anchor_monday);
        $days = (int)$anchor->diffInDays($date);
        $weeks = intdiv($days, 7);

        $rotation = [];
        foreach ($kidNames as $idx => $kidName) {
            $name = (string)$kidName;
            $kid = $kidsByName->get($name);
            if ($kid === null) {
                continue;
            }

            $slotIdx = ($idx + $weeks) % count($slotTitles);
            $title = (string)$slotTitles[$slotIdx];
            $slot = $slotsByTitle->get($title);
            if ($slot === null) {
                continue;
            }

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
