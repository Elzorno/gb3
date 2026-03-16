<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InfractionDef;
use App\Models\Kid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfractionPrivilegeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_infraction_increments_strike_and_sets_locks(): void
    {
        $kid = Kid::query()->create([
            'display_name' => 'Inf Kid',
            'sort_order' => 0,
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
        ]);

        $def = InfractionDef::query()->create([
            'code' => 'late_homework',
            'label' => 'Late Homework',
            'active' => true,
            'mode' => 'set',
            'days' => 2,
            'ladder_json' => '[1,2,4]',
            'blocks_json' => '{"phone":1,"games":1,"other":0}',
            'review_days' => 0,
            'sort_order' => 1,
        ]);

        $res = $this->post('/infractions/apply', [
            'kid_id' => $kid->id,
            'infraction_def_id' => $def->id,
            'note' => 'first strike',
        ]);
        $res->assertRedirect('/infractions');

        $this->assertDatabaseHas('infraction_strikes', [
            'kid_id' => $kid->id,
            'infraction_def_id' => $def->id,
            'strike_count' => 1,
        ]);

        $this->assertDatabaseHas('privileges', [
            'kid_id' => $kid->id,
            'phone_locked' => 1,
            'games_locked' => 1,
            'other_locked' => 0,
        ]);

        $event = \DB::table('infraction_events')->first();
        $this->assertNotNull($event);
        $this->assertSame(1, (int)$event->strike_after);
        $this->assertSame(1, (int)$event->days_applied);
        $this->assertNotNull($event->review_on);
    }

    public function test_review_unlock_and_reset_strike_updates_state(): void
    {
        $kid = Kid::query()->create([
            'display_name' => 'Review Kid',
            'sort_order' => 0,
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
        ]);

        $def = InfractionDef::query()->create([
            'code' => 'screens_after_bed',
            'label' => 'Screens After Bed',
            'active' => true,
            'mode' => 'set',
            'days' => 1,
            'blocks_json' => '{"phone":1,"games":0,"other":0}',
            'review_days' => 1,
            'sort_order' => 1,
        ]);

        $this->post('/infractions/apply', [
            'kid_id' => $kid->id,
            'infraction_def_id' => $def->id,
        ])->assertRedirect('/infractions');

        $eventId = (int)\DB::table('infraction_events')->value('id');

        $res = $this->post('/infractions/review', [
            'event_id' => $eventId,
            'action' => 'unlock',
            'keep_minutes' => 0,
            'reset_strike' => '1',
            'review_note' => 'resolved',
        ]);
        $res->assertRedirect('/infractions/review');

        $this->assertDatabaseHas('infraction_events', [
            'id' => $eventId,
            'review_action' => 'unlock',
            'review_note' => 'resolved',
        ]);

        $this->assertDatabaseHas('privileges', [
            'kid_id' => $kid->id,
            'phone_locked' => 0,
        ]);

        $this->assertDatabaseHas('infraction_strikes', [
            'kid_id' => $kid->id,
            'infraction_def_id' => $def->id,
            'strike_count' => 0,
        ]);
    }
}
