<?php

declare(strict_types=1);

namespace App\Domain\Bonus;

use App\Models\BonusDef;
use App\Models\BonusInstance;
use App\Models\Submission;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;

class BonusService
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {
    }

    public function weekStart(string|CarbonImmutable|null $date = null): string
    {
        $d = $date instanceof CarbonImmutable ? $date : CarbonImmutable::parse((string)($date ?? 'now'));
        return $d->startOfWeek(CarbonImmutable::MONDAY)->format('Y-m-d');
    }

    public function ensureWeekInstances(string $weekStart): void
    {
        $defs = BonusDef::query()->where('active', true)->orderBy('sort_order')->orderBy('id')->get();
        foreach ($defs as $def) {
            BonusInstance::query()->firstOrCreate([
                'week_start' => $weekStart,
                'bonus_def_id' => $def->id,
            ], [
                'status' => 'available',
            ]);
        }
    }

    public function listWeek(string $weekStart): Collection
    {
        $this->ensureWeekInstances($weekStart);

        return BonusInstance::query()
            ->with(['definition', 'kid'])
            ->whereDate('week_start', $weekStart)
            ->orderBy('id')
            ->get();
    }

    public function claim(int $instanceId, int $kidId): BonusInstance
    {
        return $this->db->transaction(function () use ($instanceId, $kidId): BonusInstance {
            /** @var BonusInstance $inst */
            $inst = BonusInstance::query()->lockForUpdate()->findOrFail($instanceId);
            if ($inst->status !== 'available') {
                throw new \RuntimeException('Bonus is not available');
            }

            $inst->status = 'claimed';
            $inst->claimed_by_kid_id = $kidId;
            $inst->claimed_at = CarbonImmutable::now('UTC');
            $inst->save();

            return $inst;
        });
    }

    public function submitProof(int $instanceId, int $kidId, string $proofPath): Submission
    {
        return $this->db->transaction(function () use ($instanceId, $kidId, $proofPath): Submission {
            /** @var BonusInstance $inst */
            $inst = BonusInstance::query()->lockForUpdate()->findOrFail($instanceId);
            if ($inst->claimed_by_kid_id !== $kidId || !in_array($inst->status, ['claimed', 'rejected'], true)) {
                throw new \RuntimeException('Bonus cannot be submitted by this kid');
            }

            $sub = Submission::query()->create([
                'kind' => 'bonus',
                'week_start' => $inst->week_start?->format('Y-m-d') ?? $this->weekStart(),
                'kid_id' => $kidId,
                'bonus_instance_id' => $inst->id,
                'proof_path' => $proofPath,
                'status' => 'pending',
                'submitted_at' => CarbonImmutable::now('UTC'),
            ]);

            $inst->status = 'pending';
            $inst->submission_id = $sub->id;
            $inst->save();

            return $sub;
        });
    }
}
