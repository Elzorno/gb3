<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\ChoreSlot;
use App\Models\Kid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kid_can_submit_base_chore_and_queue_it_for_review(): void
    {
        $kid = Kid::query()->create([
            'display_name' => 'Megan',
            'sort_order' => 0,
        ]);

        $slot = ChoreSlot::query()->create([
            'title' => 'Dishes',
            'active' => true,
            'sort_order' => 0,
        ]);

        Assignment::query()->create([
            'day' => '2026-03-16',
            'kid_id' => $kid->id,
            'slot_id' => $slot->id,
            'status' => 'open',
        ]);

        $res = $this->withSession(['gb2_kid_id' => $kid->id])->post('/submission/base', [
            'day' => '2026-03-16',
            'slot_id' => $slot->id,
            'proof_path' => 'uploads/NO_PHOTO',
        ]);

        $res->assertRedirect('/submission');

        $this->assertDatabaseHas('submissions', [
            'kid_id' => $kid->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('assignments', [
            'day' => '2026-03-16',
            'kid_id' => $kid->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);
    }

    public function test_review_decision_updates_submission_and_assignment_state(): void
    {
        $kid = Kid::query()->create([
            'display_name' => 'Stacey',
            'sort_order' => 1,
        ]);

        $slot = ChoreSlot::query()->create([
            'title' => 'Trash',
            'active' => true,
            'sort_order' => 1,
        ]);

        $submissionId = \DB::table('submissions')->insertGetId([
            'kind' => 'base',
            'day' => '2026-03-16',
            'week_start' => null,
            'kid_id' => $kid->id,
            'slot_id' => $slot->id,
            'bonus_instance_id' => null,
            'proof_path' => 'uploads/NO_PHOTO',
            'status' => 'pending',
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by_admin_id' => 0,
            'review_note' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Assignment::query()->create([
            'day' => '2026-03-16',
            'kid_id' => $kid->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
            'submission_id' => $submissionId,
        ]);

        $res = $this->post('/review/decide', [
            'submission_id' => $submissionId,
            'decision' => 'approved',
            'note' => 'Looks good',
        ]);

        $res->assertRedirect('/review');

        $this->assertDatabaseHas('submissions', [
            'id' => $submissionId,
            'status' => 'approved',
            'review_note' => 'Looks good',
        ]);

        $this->assertDatabaseHas('assignments', [
            'day' => '2026-03-16',
            'kid_id' => $kid->id,
            'slot_id' => $slot->id,
            'status' => 'approved',
        ]);
    }
}
