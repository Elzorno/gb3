<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BonusDef;
use App\Models\BonusInstance;
use App\Models\Kid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $kidSession = ['gb2_kid_id' => $kid->id];

        $claimRes = $this->withSession($kidSession)->post('/app/bonuses/claim', [
            'instance_id' => $inst->id,
        ]);
        $claimRes->assertRedirect(route('app.bonuses'));

        $this->assertDatabaseHas('bonus_instances', [
            'id' => $inst->id,
            'status' => 'claimed',
            'claimed_by_kid_id' => $kid->id,
        ]);

        Storage::fake('public');

        $submitRes = $this->withSession($kidSession)->post('/app/bonuses/submit', [
            'instance_id' => $inst->id,
            'photo' => UploadedFile::fake()->image('bonus_proof.jpg'),
        ]);
        $submitRes->assertRedirect(route('app.bonuses'));

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

        $adminSession = ['gb2_admin_logged_in' => true];

        // Ensure admin auth middleware doesn't redirect to setup
        \DB::table('settings')->insert(['key' => 'admin_password_hash', 'value' => 'test']);

        $reviewRes = $this->withSession($adminSession)->post('/admin/reviews/decide', [
            'submission_id' => $submission->id,
            'decision' => 'approved',
            'note' => 'Great work',
        ]);
        $reviewRes->assertRedirect(route('admin.reviews'));

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
