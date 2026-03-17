<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChoreSlot;
use App\Models\Kid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KidHistoryFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_shows_only_current_kid_and_filters(): void
    {
        $kidA = Kid::query()->create([
            'display_name' => 'Kid A',
            'sort_order' => 0,
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
        ]);

        $kidB = Kid::query()->create([
            'display_name' => 'Kid B',
            'sort_order' => 1,
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
        ]);

        $slotA = ChoreSlot::query()->create([
            'title' => 'Dishes For A',
            'active' => true,
            'sort_order' => 0,
        ]);

        $slotB = ChoreSlot::query()->create([
            'title' => 'Trash For B',
            'active' => true,
            'sort_order' => 1,
        ]);

        \DB::table('submissions')->insert([
            [
                'kind' => 'base',
                'day' => '2026-03-16',
                'week_start' => null,
                'kid_id' => $kidA->id,
                'slot_id' => $slotA->id,
                'bonus_instance_id' => null,
                'proof_path' => 'uploads/a-approved.jpg',
                'status' => 'approved',
                'submitted_at' => now(),
                'reviewed_at' => now(),
                'reviewed_by_admin_id' => 1,
                'review_note' => 'ok',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kind' => 'bonus',
                'day' => null,
                'week_start' => '2026-03-16',
                'kid_id' => $kidA->id,
                'slot_id' => null,
                'bonus_instance_id' => 10,
                'proof_path' => 'uploads/a-pending.jpg',
                'status' => 'pending',
                'submitted_at' => now(),
                'reviewed_at' => null,
                'reviewed_by_admin_id' => 0,
                'review_note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kind' => 'base',
                'day' => '2026-03-16',
                'week_start' => null,
                'kid_id' => $kidB->id,
                'slot_id' => $slotB->id,
                'bonus_instance_id' => null,
                'proof_path' => 'uploads/b-approved.jpg',
                'status' => 'approved',
                'submitted_at' => now(),
                'reviewed_at' => now(),
                'reviewed_by_admin_id' => 1,
                'review_note' => 'ok',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $res = $this->withSession(['gb2_kid_id' => $kidA->id])->get('/app/history?filter=chores');

        $res->assertOk();
        // View renders slot title for base submissions, not proof_path
        $res->assertSee('Dishes For A');
        $res->assertDontSee('Trash For B');
    }
}
