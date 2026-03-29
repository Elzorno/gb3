<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Rotation\RotationPlanner;
use App\Models\ChoreSlot;
use App\Models\Kid;
use App\Models\RotationRule;
use App\Models\Assignment;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RotationAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rotation_today_route_is_reachable(): void
    {
        $kid = Kid::query()->create([
            'display_name' => 'Tester',
            'sort_order' => 0,
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
        ]);

        $res = $this->withSession(['gb2_kid_id' => $kid->id])->get('/app/today');

        $res->assertOk();
        $res->assertSee('Family context');
        $res->assertSee('My rhythm');
    }

    public function test_today_page_shows_next_calm_step_for_open_assignment(): void
    {
        $kid = Kid::query()->create([
            'display_name' => 'Tester',
            'sort_order' => 0,
            'pin_hash' => password_hash('123456', PASSWORD_ARGON2ID),
        ]);

        $slot = ChoreSlot::query()->create([
            'title' => 'Kitchen reset',
            'active' => true,
            'sort_order' => 0,
        ]);

        Assignment::query()->create([
            'day' => CarbonImmutable::today(config('app.timezone'))->format('Y-m-d'),
            'kid_id' => $kid->id,
            'slot_id' => $slot->id,
            'status' => 'open',
        ]);

        $res = $this->withSession(['gb2_kid_id' => $kid->id])->get('/app/today');

        $res->assertOk();
        $res->assertSee('Next calm step');
        $res->assertSee('Kitchen reset');
    }

    public function test_planner_generates_weekday_assignments_from_rule(): void
    {
        Kid::query()->create(['display_name' => 'A', 'sort_order' => 0]);
        Kid::query()->create(['display_name' => 'B', 'sort_order' => 1]);

        ChoreSlot::query()->create(['title' => 'Dishes', 'active' => true, 'sort_order' => 0]);
        ChoreSlot::query()->create(['title' => 'Trash', 'active' => true, 'sort_order' => 1]);

        RotationRule::query()->create([
            'name' => 'default',
            'kids_json' => json_encode(['A', 'B'], JSON_THROW_ON_ERROR),
            'slots_json' => json_encode(['Dishes', 'Trash'], JSON_THROW_ON_ERROR),
            'anchor_monday' => '2026-03-16',
        ]);

        $planner = app(RotationPlanner::class);
        $rows = $planner->ensureAssignmentsForDay(CarbonImmutable::parse('2026-03-16'));

        $this->assertCount(2, $rows);
        $this->assertSame('Dishes', $rows[0]->slot?->title);
        $this->assertSame('Trash', $rows[1]->slot?->title);
    }
}
