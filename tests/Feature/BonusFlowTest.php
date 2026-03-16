<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BonusDef;
use App\Models\BonusInstance;
use App\Models\Kid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_bonus_claim_submit_and_review_flow_updates_states(): void
    {
        $kid = Kid::query()->create([
            'display_name' => 'Barry',
            'sort_order' => 2,
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
        ]);

        $def = BonusDef::query()->create([
            'title' => 'Garage cleanup',
            'active' => true,
            'reward_cents' => 500,
            'sort_order' => 0,
        ]);

        $inst = BonusInstance::query()->create([
            'week_start' => '2026-03-16',
            'bonus_def_id' => $def->id,
            'status' => 'available',
        ]);

        $claimRes = $this->withSession(['gb2_kid_id' => $kid->id])->post('/bonus/claim', [
            'instance_id' => $inst->id,
        ]);
        $claimRes->assertRedirect('/bonus');

        $this->assertDatabaseHas('bonus_instances', [
            'id' => $inst->id,
            'status' => 'claimed',
            'claimed_by_kid_id' => $kid->id,
        ]);

        $submitRes = $this->withSession(['gb2_kid_id' => $kid->id])->post('/bonus/submit', [
            'instance_id' => $inst->id,
            'proof_path' => 'uploads/NO_PHOTO',
        ]);
        $submitRes->assertRedirect('/bonus');

        $submission = \DB::table('submissions')
            ->where('kind', 'bonus')
            ->where('bonus_instance_id', $inst->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertSame('pending', $submission->status);

        $this->assertDatabaseHas('bonus_instances', [
            'id' => $inst->id,
            'status' => 'pending',
            'submission_id' => $submission->id,
        ]);

        $reviewRes = $this->post('/review/decide', [
            'submission_id' => $submission->id,
            'decision' => 'approved',
            'note' => 'Great work',
        ]);
        $reviewRes->assertRedirect('/review');

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => 'approved',
            'review_note' => 'Great work',
        ]);

        $this->assertDatabaseHas('bonus_instances', [
            'id' => $inst->id,
            'status' => 'approved',
        ]);
    }
}
