<?php

declare(strict_types=1);

namespace App\Domain\Privilege;

use App\Models\Privilege;
use Carbon\CarbonImmutable;

class PrivilegeService
{
    public function ensureRow(int $kidId): Privilege
    {
        /** @var Privilege $row */
        $row = Privilege::query()->firstOrCreate(
            ['kid_id' => $kidId],
            [
                'phone_locked' => false,
                'games_locked' => false,
                'other_locked' => false,
                'bank_phone_min' => 0,
                'bank_games_min' => 0,
                'bank_other_min' => 0,
                'updated_at' => CarbonImmutable::now('UTC'),
            ],
        );

        return $this->autoUnlockIfNeeded($row);
    }

    public function autoUnlockIfNeeded(Privilege $row): Privilege
    {
        $now = CarbonImmutable::now('UTC');
        $dirty = false;

        foreach (['phone', 'games', 'other'] as $which) {
            $lockedCol = $which . '_locked';
            $untilCol = $which . '_locked_until';
            $until = $row->{$untilCol};
            if ($row->{$lockedCol} && $until !== null && $until->lessThanOrEqualTo($now)) {
                $row->{$lockedCol} = false;
                $row->{$untilCol} = null;
                $dirty = true;
            }
        }

        if ($dirty) {
            $row->updated_at = $now;
            $row->save();
        }

        return $row;
    }

    public function setLockUntil(int $kidId, string $which, ?CarbonImmutable $until): ?CarbonImmutable
    {
        if (!in_array($which, ['phone', 'games', 'other'], true)) {
            throw new \InvalidArgumentException('Invalid lock type');
        }

        $row = $this->ensureRow($kidId);
        $lockedCol = $which . '_locked';
        $untilCol = $which . '_locked_until';

        if ($until === null) {
            $row->{$lockedCol} = false;
            $row->{$untilCol} = null;
        } else {
            $row->{$lockedCol} = true;
            $row->{$untilCol} = $until;
        }

        $row->updated_at = CarbonImmutable::now('UTC');
        $row->save();

        return $until;
    }

    public function addLockMinutes(int $kidId, string $which, int $minutes): ?CarbonImmutable
    {
        if (!in_array($which, ['phone', 'games', 'other'], true)) {
            throw new \InvalidArgumentException('Invalid lock type');
        }

        $row = $this->ensureRow($kidId);
        $lockedCol = $which . '_locked';
        $untilCol = $which . '_locked_until';

        if ($minutes <= 0) {
            return $row->{$untilCol};
        }

        $now = CarbonImmutable::now('UTC');
        $current = $row->{$untilCol};
        $base = ($current instanceof CarbonImmutable || $current) && $current !== null && $current->greaterThan($now)
            ? CarbonImmutable::instance($current)
            : $now;

        $newUntil = $base->addMinutes($minutes);
        $row->{$lockedCol} = true;
        $row->{$untilCol} = $newUntil;
        $row->updated_at = $now;
        $row->save();

        return $newUntil;
    }

    public function getForKid(int $kidId): Privilege
    {
        return $this->ensureRow($kidId);
    }
}
