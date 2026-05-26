<?php

declare(strict_types=1);

namespace App\Domain\Payout;

use App\Domain\Ledger\LedgerService;
use App\Models\PayoutRequest;
use App\Models\Privilege;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;

class PayoutService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly LedgerService $ledger,
    ) {
    }

    /**
     * Check if a kid has a pending payout request.
     */
    public function hasPendingRequest(int $kidId): bool
    {
        return PayoutRequest::query()
            ->where('kid_id', $kidId)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Get the current pending payout request for a kid, if any.
     */
    public function getPendingRequest(int $kidId): ?PayoutRequest
    {
        return PayoutRequest::query()
            ->where('kid_id', $kidId)
            ->where('status', 'pending')
            ->first();
    }

    /**
     * Create a new payout request for a kid, snapshotting current bank balances.
     * Throws if there's already a pending request.
     */
    public function requestPayout(int $kidId): PayoutRequest
    {
        return $this->db->transaction(function () use ($kidId): PayoutRequest {
            // Check for existing pending request
            $existing = PayoutRequest::query()
                ->where('kid_id', $kidId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new \RuntimeException('You already have a pending payout request.');
            }

            // Get current bank balances
            $priv = Privilege::query()
                ->where('kid_id', $kidId)
                ->lockForUpdate()
                ->first();

            if (!$priv) {
                throw new \RuntimeException('No balance available to request payout.');
            }

            $cents = (int) $priv->bank_cents;
            $phoneMin = (int) $priv->bank_phone_min;
            $gamesMin = (int) $priv->bank_games_min;
            $otherMin = (int) $priv->bank_other_min;

            if ($cents <= 0 && $phoneMin <= 0 && $gamesMin <= 0 && $otherMin <= 0) {
                throw new \RuntimeException('No balance available to request payout.');
            }

            return PayoutRequest::query()->create([
                'kid_id' => $kidId,
                'status' => 'pending',
                'requested_cents' => $cents,
                'requested_phone_min' => $phoneMin,
                'requested_games_min' => $gamesMin,
                'requested_other_min' => $otherMin,
                'requested_at' => CarbonImmutable::now('UTC'),
            ]);
        });
    }

    /**
     * Approve a payout request: debit the snapshot amounts and record in ledger.
     */
    public function approve(
        int $requestId,
        ?string $note = null,
        string $actorType = 'admin',
        int $actorId = 0,
        ?string $actorSessionKey = null,
    ): PayoutRequest {
        return $this->db->transaction(function () use ($requestId, $note, $actorType, $actorId, $actorSessionKey): PayoutRequest {
            /** @var PayoutRequest $req */
            $req = PayoutRequest::query()->lockForUpdate()->findOrFail($requestId);

            if ($req->status !== 'pending') {
                throw new \RuntimeException('Payout request has already been reviewed.');
            }

            // Debit the snapshot amounts (clamped to avoid negative)
            $this->ledger->debit(
                (int) $req->kid_id,
                (int) $req->requested_cents,
                (int) $req->requested_phone_min,
                (int) $req->requested_games_min,
                (int) $req->requested_other_min,
                'payout_approved',
                $req->id,
                'Payout approved',
                $actorType,
                $actorId,
            );

            $req->status = 'approved';
            $req->reviewed_at = CarbonImmutable::now('UTC');
            $req->reviewed_by_actor_type = $actorType;
            $req->reviewed_by_actor_id = $actorId;
            $req->reviewed_by_session_key = $actorSessionKey;
            $req->review_note = $note;
            $req->save();

            return $req;
        });
    }

    /**
     * Deny a payout request: leave bank intact, mark denied.
     */
    public function deny(
        int $requestId,
        ?string $note = null,
        string $actorType = 'admin',
        int $actorId = 0,
        ?string $actorSessionKey = null,
    ): PayoutRequest {
        return $this->db->transaction(function () use ($requestId, $note, $actorType, $actorId, $actorSessionKey): PayoutRequest {
            /** @var PayoutRequest $req */
            $req = PayoutRequest::query()->lockForUpdate()->findOrFail($requestId);

            if ($req->status !== 'pending') {
                throw new \RuntimeException('Payout request has already been reviewed.');
            }

            $req->status = 'denied';
            $req->reviewed_at = CarbonImmutable::now('UTC');
            $req->reviewed_by_actor_type = $actorType;
            $req->reviewed_by_actor_id = $actorId;
            $req->reviewed_by_session_key = $actorSessionKey;
            $req->review_note = $note;
            $req->save();

            return $req;
        });
    }

    /**
     * List pending payout requests for admin review.
     */
    public function listPending(int $limit = 50)
    {
        return PayoutRequest::query()
            ->with('kid')
            ->where('status', 'pending')
            ->orderBy('requested_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent payout requests for a kid.
     */
    public function kidHistory(int $kidId, int $limit = 10)
    {
        return PayoutRequest::query()
            ->where('kid_id', $kidId)
            ->orderByDesc('requested_at')
            ->limit($limit)
            ->get();
    }
}
