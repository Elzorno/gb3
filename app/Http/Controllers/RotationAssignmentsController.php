<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Rotation\RotationPlanner;
use App\Models\Assignment;
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

        return view('app.today', [
            'kid' => $kid,
            'date' => $date,
            'isWeekday' => $this->planner->isWeekday($date),
            'assignments' => $assignments,
            'isGrounded' => $isGrounded,
            'rejectionNotes' => $rejectionNotes,
        ]);
    }
}
