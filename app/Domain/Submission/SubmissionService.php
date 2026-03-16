<?php

declare(strict_types=1);

namespace App\Domain\Submission;

use App\Models\Assignment;
use App\Models\Submission;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;

class SubmissionService
{
    public function __construct(
        private readonly DatabaseManager $db,
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

            return $sub->fresh(['kid', 'slot']);
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
