<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Rotation\RotationPlanner;
use App\Models\Assignment;
use App\Models\InfractionEvent;
use App\Models\Kid;
use App\Models\Submission;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RotationAssignmentsController extends Controller
{
    public function __construct(
        private readonly RotationPlanner $planner,
    ) {
    }

    public function today(Request $request): View
    {
        $kidId = (int) $request->session()->get('gb2_kid_id');
        $kid = Kid::with('privileges')->find($kidId);
        
        $date = CarbonImmutable::today(config('app.timezone'));
        
        // Ensure assignments exist for today
        $this->planner->ensureAssignmentsForDay($date);
        
        // Get only this kid's assignments for today
        $assignments = Assignment::with('slot')
            ->where('day', $date->format('Y-m-d'))
            ->where('kid_id', $kidId)
            ->get();

        // Check if kid is grounded
        $isGrounded = false;
        if ($kid?->privileges) {
            $priv = $kid->privileges;
            $isGrounded = $priv->phone_locked || $priv->games_locked || $priv->other_locked;
        }

        // Build rejection notes map (slot_id => note) for rejected assignments
        $rejectionNotes = [];
        $rejectedSlotIds = $assignments->where('status', 'rejected')->pluck('slot_id')->all();
        if ($rejectedSlotIds) {
            $rejectionNotes = Submission::where('kid_id', $kidId)
                ->where('day', $date->format('Y-m-d'))
                ->where('status', 'rejected')
                ->whereIn('slot_id', $rejectedSlotIds)
                ->whereNotNull('review_note')
                ->get()
                ->keyBy('slot_id')
                ->map(fn($s) => $s->review_note)
                ->all();
        }

        // Get active consequence context for kid-facing display
        $activeConsequence = $this->getActiveConsequenceContext($kidId);

        return view('app.today', [
            'kid' => $kid,
            'date' => $date,
            'isWeekday' => $this->planner->isWeekday($date),
            'assignments' => $assignments,
            'isGrounded' => $isGrounded,
            'rejectionNotes' => $rejectionNotes,
            'activeConsequence' => $activeConsequence,
        ]);
    }

    /**
     * Get active consequence details for kid-facing display.
     * Returns structured data with calm, concrete language.
     */
    private function getActiveConsequenceContext(int $kidId): ?array
    {
        // Get the most recent active (unreviewed or recently reviewed) consequence
        $event = InfractionEvent::with('definition')
            ->where('kid_id', $kidId)
            ->whereNull('reviewed_at')
            ->orderByDesc('ts')
            ->first();

        if (!$event) {
            return null;
        }

        // Parse blocked privileges
        $blocksJson = $event->blocks_json;
        $blocks = is_string($blocksJson) ? json_decode($blocksJson, true) : $blocksJson;
        if (!is_array($blocks)) {
            $blocks = [];
        }

        $pausedPrivileges = [];
        $privilegeLabels = [
            'phone' => 'Phone',
            'games' => 'Games',
            'other' => 'Other screen time',
        ];
        foreach (['phone', 'games', 'other'] as $type) {
            if (($blocks[$type] ?? 0) === 1) {
                $pausedPrivileges[] = $privilegeLabels[$type];
            }
        }

        // Parse computed_until for timing info
        $computedUntilJson = $event->computed_until_json;
        $computedUntil = is_string($computedUntilJson) ? json_decode($computedUntilJson, true) : $computedUntilJson;
        $earliestUntil = null;
        if (is_array($computedUntil)) {
            foreach ($computedUntil as $until) {
                if ($until) {
                    $dt = CarbonImmutable::parse($until);
                    if ($earliestUntil === null || $dt->lessThan($earliestUntil)) {
                        $earliestUntil = $dt;
                    }
                }
            }
        }

        // Build friendly review timing text
        $reviewText = null;
        if ($event->review_on) {
            $reviewDate = CarbonImmutable::parse($event->review_on);
            $now = CarbonImmutable::today(config('app.timezone'));
            
            if ($reviewDate->isSameDay($now)) {
                $reviewText = 'Review today';
            } elseif ($reviewDate->isTomorrow()) {
                $reviewText = 'Review tomorrow';
            } else {
                $reviewText = 'Review on ' . $reviewDate->format('l, M j');
            }
        }

        // Build next step / get-back-on-track text from repairs_json if available
        $nextStepText = null;
        $def = $event->definition;
        if ($def && $def->repairs_json) {
            $repairs = json_decode($def->repairs_json, true);
            if (is_array($repairs) && !empty($repairs)) {
                // Take first repair as the suggested next step
                $firstRepair = $repairs[0] ?? null;
                if (is_string($firstRepair) && strlen($firstRepair) > 0) {
                    $nextStepText = $firstRepair;
                }
            }
        }

        // Default calm next-step if no repairs defined
        if (!$nextStepText) {
            $nextStepText = 'Keep completing your daily tasks to get back on track.';
        }

        return [
            'label' => $def?->label ?? 'Consequence',
            'pausedPrivileges' => $pausedPrivileges,
            'reviewText' => $reviewText,
            'nextStepText' => $nextStepText,
            'daysApplied' => $event->days_applied,
            'earliestUntil' => $earliestUntil,
        ];
    }
}
