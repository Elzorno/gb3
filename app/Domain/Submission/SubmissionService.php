<?php

declare(strict_types=1);

namespace App\Domain\Submission;

use App\Domain\Ledger\LedgerService;
use App\Models\Assignment;
use App\Models\BonusInstance;
use App\Models\Submission;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;

class SubmissionService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly LedgerService $ledger,
    ) {
    }

    public function submitBase(int $kidId, int $slotId, string $dayYmd, string $proofPath): Submission
    {
        return $this->db->transaction(function () use ($kidId, $slotId, $dayYmd, $proofPath): Submission {
            $sub = Submission::query()->create([
                'kind' => 'base',
                'day' => $dayYmd,
                'kid_id' => $kidId,
                'slot_id' => $slotId,
                'proof_path' => $proofPath,
                'status' => 'pending',
                'submitted_at' => CarbonImmutable::now('UTC'),
            ]);

            Assignment::query()
                ->whereDate('day', $dayYmd)
                ->where('kid_id', $kidId)
                ->where('slot_id', $slotId)
                ->update([
                    'status' => 'pending',
                    'submission_id' => $sub->id,
                    'updated_at' => now(),
                ]);

            return $sub;
        });
    }

    public function review(int $submissionId, string $decision, ?string $note = null): Submission
    {
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            throw new \InvalidArgumentException('Invalid decision');
        }

        return $this->db->transaction(function () use ($submissionId, $decision, $note): Submission {
            /** @var Submission $sub */
            $sub = Submission::query()->lockForUpdate()->findOrFail($submissionId);

            $sub->status = $decision;
            $sub->review_note = $note;
            $sub->reviewed_at = CarbonImmutable::now('UTC');
            $sub->reviewed_by_admin_id = 1;
            $sub->save();

            if ($sub->kind === 'base' && $sub->day && $sub->kid_id && $sub->slot_id) {
                Assignment::query()
                    ->whereDate('day', $sub->day->format('Y-m-d'))
                    ->where('kid_id', $sub->kid_id)
                    ->where('slot_id', $sub->slot_id)
                    ->update([
                        'status' => $decision,
                        'updated_at' => now(),
                    ]);
            }

            if ($sub->kind === 'bonus' && $sub->bonus_instance_id) {
                $instance = BonusInstance::query()
                    ->with('definition')
                    ->where('id', $sub->bonus_instance_id)
                    ->first();

                if ($instance) {
                    $instance->status = $decision;
                    $instance->updated_at = now();
                    $instance->save();

                    // Credit rewards to kid's bank on approval
                    if ($decision === 'approved' && $instance->definition) {
                        $def = $instance->definition;
                        $this->ledger->credit(
                            (int) $sub->kid_id,
                            (int) ($def->reward_cents ?? 0),
                            (int) ($def->reward_phone_min ?? 0),
                            (int) ($def->reward_games_min ?? 0),
                            0,
                            'bonus_approved',
                            $sub->id,
                            "Bonus: {$def->title}",
                        );
                    }
                }
            }

            return $sub->fresh(['kid', 'slot']);
        });
    }

    public function undoReview(int $submissionId): Submission
    {
        return $this->db->transaction(function () use ($submissionId): Submission {
            $sub = Submission::query()->lockForUpdate()->findOrFail($submissionId);

            $previousDecision = $sub->status;
            $sub->status = 'pending';
            $sub->review_note = null;
            $sub->reviewed_at = null;
            $sub->reviewed_by_admin_id = null;
            $sub->save();

            // Revert assignment status
            if ($sub->kind === 'base' && $sub->day && $sub->kid_id && $sub->slot_id) {
                Assignment::query()
                    ->whereDate('day', $sub->day->format('Y-m-d'))
                    ->where('kid_id', $sub->kid_id)
                    ->where('slot_id', $sub->slot_id)
                    ->update(['status' => 'pending', 'updated_at' => now()]);
            }

            // Revert bonus instance status
            if ($sub->kind === 'bonus' && $sub->bonus_instance_id) {
                $instance = BonusInstance::query()
                    ->with('definition')
                    ->find($sub->bonus_instance_id);

                if ($instance) {
                    $instance->status = 'pending';
                    $instance->save();

                    // If was approved, reverse the ledger credit
                    if ($previousDecision === 'approved' && $instance->definition) {
                        $def = $instance->definition;
                        $this->ledger->debit(
                            (int) $sub->kid_id,
                            (int) ($def->reward_cents ?? 0),
                            (int) ($def->reward_phone_min ?? 0),
                            (int) ($def->reward_games_min ?? 0),
                            0,
                            'undo_review',
                            $sub->id,
                            "Undo: {$def->title}",
                        );
                    }
                }
            }

            return $sub;
        });
    }

    public function pendingList(int $limit = 50)
    {
        return Submission::query()
            ->with(['kid', 'slot'])
            ->where('status', 'pending')
            ->orderByDesc('submitted_at')
            ->limit($limit)
            ->get();
    }
}
