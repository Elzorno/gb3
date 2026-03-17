<?php

declare(strict_types=1);

namespace App\Domain\Ledger;

use App\Models\LedgerEntry;
use App\Models\Privilege;
use Carbon\CarbonImmutable;

class LedgerService
{
    /**
     * Credit reward balances to a kid and record a ledger entry.
     */
    public function credit(
        int $kidId,
        int $cents,
        int $phoneMin,
        int $gamesMin,
        int $otherMin,
        string $source,
        ?int $sourceId = null,
        ?string $note = null,
        string $actorType = 'system',
        int $actorId = 0,
    ): LedgerEntry {
        $priv = Privilege::query()->firstOrCreate(
            ['kid_id' => $kidId],
            [
                'phone_locked' => false,
                'games_locked' => false,
                'other_locked' => false,
                'bank_phone_min' => 0,
                'bank_games_min' => 0,
                'bank_other_min' => 0,
                'bank_cents' => 0,
            ],
        );

        $priv->bank_cents += $cents;
        $priv->bank_phone_min += $phoneMin;
        $priv->bank_games_min += $gamesMin;
        $priv->bank_other_min += $otherMin;
        $priv->updated_at = CarbonImmutable::now('UTC');
        $priv->save();

        return LedgerEntry::query()->create([
            'kid_id' => $kidId,
            'type' => 'credit',
            'source' => $source,
            'source_id' => $sourceId,
            'cents' => $cents,
            'phone_min' => $phoneMin,
            'games_min' => $gamesMin,
            'other_min' => $otherMin,
            'note' => $note,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'created_at' => CarbonImmutable::now('UTC'),
        ]);
    }

    /**
     * Debit (subtract) balances from a kid and record a ledger entry.
     */
    public function debit(
        int $kidId,
        int $cents,
        int $phoneMin,
        int $gamesMin,
        int $otherMin,
        string $source,
        ?int $sourceId = null,
        ?string $note = null,
        string $actorType = 'system',
        int $actorId = 0,
    ): LedgerEntry {
        $priv = Privilege::query()->firstOrCreate(
            ['kid_id' => $kidId],
            [
                'phone_locked' => false,
                'games_locked' => false,
                'other_locked' => false,
                'bank_phone_min' => 0,
                'bank_games_min' => 0,
                'bank_other_min' => 0,
                'bank_cents' => 0,
            ],
        );

        $priv->bank_cents = max(0, $priv->bank_cents - $cents);
        $priv->bank_phone_min = max(0, $priv->bank_phone_min - $phoneMin);
        $priv->bank_games_min = max(0, $priv->bank_games_min - $gamesMin);
        $priv->bank_other_min = max(0, $priv->bank_other_min - $otherMin);
        $priv->updated_at = CarbonImmutable::now('UTC');
        $priv->save();

        return LedgerEntry::query()->create([
            'kid_id' => $kidId,
            'type' => 'debit',
            'source' => $source,
            'source_id' => $sourceId,
            'cents' => -$cents,
            'phone_min' => -$phoneMin,
            'games_min' => -$gamesMin,
            'other_min' => -$otherMin,
            'note' => $note,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'created_at' => CarbonImmutable::now('UTC'),
        ]);
    }

    /**
     * Get ledger history for a kid.
     */
    public function history(int $kidId, int $limit = 50)
    {
        return LedgerEntry::query()
            ->where('kid_id', $kidId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
