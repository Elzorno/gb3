<?php

declare(strict_types=1);

namespace App\Domain\Infraction;

use App\Domain\Privilege\PrivilegeService;
use App\Models\InfractionDef;
use App\Models\InfractionEvent;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;

class InfractionService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly PrivilegeService $privileges,
    ) {
    }

    public function activeDefinitions()
    {
        return InfractionDef::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    public function apply(int $kidId, int $defId, string $note = '', string $actorType = 'admin', int $actorId = 0): InfractionEvent
    {
        return $this->db->transaction(function () use ($kidId, $defId, $note, $actorType, $actorId): InfractionEvent {
            /** @var InfractionDef $def */
            $def = InfractionDef::query()->lockForUpdate()->findOrFail($defId);

            $strikeBefore = (int)DB::table('infraction_strikes')
                ->where('kid_id', $kidId)
                ->where('infraction_def_id', $defId)
                ->value('strike_count');

            $strikeAfter = $strikeBefore + 1;
            $daysApplied = $this->computeDays($def, $strikeAfter);
            $reviewDays = $this->computeReviewDays($def, $daysApplied);
            $reviewOn = $reviewDays > 0 ? CarbonImmutable::now('UTC')->addDays($reviewDays)->format('Y-m-d') : null;

            DB::table('infraction_strikes')->updateOrInsert(
                ['kid_id' => $kidId, 'infraction_def_id' => $defId],
                [
                    'strike_count' => $strikeAfter,
                    'updated_at' => CarbonImmutable::now('UTC'),
                ],
            );

            $mode = $this->normalizeMode((string)$def->mode);
            $blocks = $this->decodeBlocks((string)$def->blocks_json);
            $minutes = $daysApplied * 1440;
            $computedUntil = [];

            foreach (['phone', 'games', 'other'] as $which) {
                if (($blocks[$which] ?? 0) !== 1 || $minutes <= 0) {
                    continue;
                }

                if ($mode === 'set') {
                    $until = CarbonImmutable::now('UTC')->addMinutes($minutes);
                    $this->privileges->setLockUntil($kidId, $which, $until);
                    $computedUntil[$which] = $until->format('Y-m-d\\TH:i:s\\Z');
                    continue;
                }

                $until = $this->privileges->addLockMinutes($kidId, $which, $minutes);
                $computedUntil[$which] = $until?->setTimezone('UTC')->format('Y-m-d\\TH:i:s\\Z');
            }

            /** @var InfractionEvent $event */
            $event = InfractionEvent::query()->create([
                'kid_id' => $kidId,
                'infraction_def_id' => $defId,
                'ts' => CarbonImmutable::now('UTC'),
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'strike_before' => $strikeBefore,
                'strike_after' => $strikeAfter,
                'days_applied' => $daysApplied,
                'mode' => $mode,
                'blocks_json' => json_encode($blocks),
                'computed_until_json' => json_encode($computedUntil),
                'review_on' => $reviewOn,
                'note' => $note,
                'review_resolved_until_json' => '{}',
            ]);

            return $event->fresh(['kid', 'definition']);
        });
    }

    public function reviewEvent(
        int $eventId,
        string $action,
        int $keepMinutes,
        ?string $note,
        bool $resetStrike,
        string $actorType = 'admin',
        int $actorId = 0,
    ): InfractionEvent {
        return $this->db->transaction(function () use ($eventId, $action, $keepMinutes, $note, $resetStrike, $actorType, $actorId): InfractionEvent {
            /** @var InfractionEvent $event */
            $event = InfractionEvent::query()->lockForUpdate()->findOrFail($eventId);

            $safeAction = in_array($action, ['review_only', 'unlock', 'shorten'], true) ? $action : 'review_only';
            $resolvedUntil = [];
            $blocks = $this->decodeBlocks((string)$event->blocks_json);

            if ($safeAction === 'unlock') {
                foreach (['phone', 'games', 'other'] as $which) {
                    if (($blocks[$which] ?? 0) !== 1) {
                        continue;
                    }
                    $this->privileges->setLockUntil((int)$event->kid_id, $which, null);
                    $resolvedUntil[$which] = null;
                }
            }

            if ($safeAction === 'shorten') {
                $target = CarbonImmutable::now('UTC')->addMinutes(max(0, $keepMinutes));
                $row = $this->privileges->getForKid((int)$event->kid_id);

                foreach (['phone', 'games', 'other'] as $which) {
                    if (($blocks[$which] ?? 0) !== 1) {
                        continue;
                    }

                    $col = $which . '_locked_until';
                    $current = $row->{$col};
                    if ($current !== null && $current->lessThan($target)) {
                        $resolvedUntil[$which] = $current->setTimezone('UTC')->format('Y-m-d\\TH:i:s\\Z');
                        continue;
                    }

                    $this->privileges->setLockUntil((int)$event->kid_id, $which, $target);
                    $resolvedUntil[$which] = $target->format('Y-m-d\\TH:i:s\\Z');
                }
            }

            if ($resetStrike) {
                DB::table('infraction_strikes')->updateOrInsert(
                    [
                        'kid_id' => (int)$event->kid_id,
                        'infraction_def_id' => (int)$event->infraction_def_id,
                    ],
                    [
                        'strike_count' => 0,
                        'updated_at' => CarbonImmutable::now('UTC'),
                    ],
                );
            }

            $event->reviewed_at = CarbonImmutable::now('UTC');
            $event->reviewed_by_actor_type = $actorType;
            $event->reviewed_by_actor_id = $actorId;
            $event->review_note = $note;
            $event->review_action = $safeAction;
            $event->review_resolved_until_json = json_encode($resolvedUntil);
            $event->save();

            return $event->fresh(['kid', 'definition']);
        });
    }

    private function normalizeMode(string $mode): string
    {
        return in_array($mode, ['set', 'add'], true) ? $mode : 'set';
    }

    private function decodeBlocks(string $json): array
    {
        $v = json_decode($json, true);
        if (!is_array($v)) {
            return ['phone' => 0, 'games' => 0, 'other' => 0];
        }

        return [
            'phone' => (int)($v['phone'] ?? 0),
            'games' => (int)($v['games'] ?? 0),
            'other' => (int)($v['other'] ?? 0),
        ];
    }

    private function computeDays(InfractionDef $def, int $strikeAfter): int
    {
        $ladder = json_decode((string)$def->ladder_json, true);
        if (is_array($ladder)) {
            $vals = [];
            foreach ($ladder as $x) {
                $n = (int)$x;
                if ($n > 0) {
                    $vals[] = $n;
                }
            }

            if ($vals !== []) {
                $idx = max(0, $strikeAfter - 1);
                if ($idx >= count($vals)) {
                    $idx = count($vals) - 1;
                }

                return $vals[$idx];
            }
        }

        return max(0, (int)$def->days);
    }

    private function computeReviewDays(InfractionDef $def, int $daysApplied): int
    {
        $reviewDays = (int)$def->review_days;
        if ($reviewDays > 0) {
            return $reviewDays;
        }
        if ($daysApplied <= 0) {
            return 0;
        }

        return max(1, (int)ceil($daysApplied / 2));
    }
}
