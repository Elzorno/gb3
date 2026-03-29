<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kid;
use App\Models\Privilege;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutAdminVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_payouts_can_be_reviewed_from_admin_reviews_page(): void
    {
        $kid = Kid::query()->create([
            'display_name' => 'Parker',
            'sort_order' => 0,
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
        ]);

        Privilege::query()->create([
            'kid_id' => $kid->id,
            'bank_cents' => 1200,
            'bank_phone_min' => 30,
            'bank_games_min' => 15,
            'bank_other_min' => 0,
        ]);

        \DB::table('settings')->insert(['key' => 'admin_password_hash', 'value' => 'test']);

        $this->withSession(['gb2_kid_id' => $kid->id])
            ->post(route('app.bonuses.payout'))
            ->assertRedirect(route('app.bonuses'));

        $reviewRes = $this->withSession(['gb2_admin_logged_in' => true])
            ->get(route('admin.reviews'));

        $reviewRes->assertOk();
        $reviewRes->assertSee('Payout Requests Waiting');
        $reviewRes->assertSee('Parker');
        $reviewRes->assertSee('Requested payout from earned bank');
        $reviewRes->assertSee('Approve');
        $reviewRes->assertSee('Deny');

        $payoutId = (int) \DB::table('payout_requests')->value('id');

        $this->withSession(['gb2_admin_logged_in' => true])
            ->post(route('admin.payouts.decide'), [
                'payout_id' => $payoutId,
                'decision' => 'approved',
            ])
            ->assertRedirect(route('admin.reviews'));

        $this->assertDatabaseHas('payout_requests', [
            'id' => $payoutId,
            'status' => 'approved',
        ]);
    }
}
