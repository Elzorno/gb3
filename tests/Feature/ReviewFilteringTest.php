<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_filter_by_status_and_kind(): void
    {
        $kid = Kid::query()->create([
            'display_name' => 'Reviewer Kid',
            'sort_order' => 0,
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
        ]);

        \DB::table('submissions')->insert([
            [
                'kind' => 'base',
                'day' => '2026-03-16',
                'week_start' => null,
                'kid_id' => $kid->id,
                'slot_id' => null,
                'bonus_instance_id' => null,
                'proof_path' => 'uploads/base-pending.jpg',
                'status' => 'pending',
                'submitted_at' => now(),
                'reviewed_at' => null,
                'reviewed_by_admin_id' => 0,
                'review_note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kind' => 'bonus',
                'day' => null,
                'week_start' => '2026-03-16',
                'kid_id' => $kid->id,
                'slot_id' => null,
                'bonus_instance_id' => 9,
                'proof_path' => 'uploads/bonus-approved.jpg',
                'status' => 'approved',
                'submitted_at' => now(),
                'reviewed_at' => now(),
                'reviewed_by_admin_id' => 1,
                'review_note' => 'ok',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        \DB::table('settings')->insert(['key' => 'admin_password_hash', 'value' => 'test']);

        $res = $this->withSession(['gb2_admin_logged_in' => true])
            ->get('/admin/reviews?status=approved&kind=bonus');

        $res->assertOk();
        $res->assertSee('uploads/bonus-approved.jpg');
        $res->assertDontSee('uploads/base-pending.jpg');
    }
}
