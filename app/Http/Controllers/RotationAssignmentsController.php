<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Rotation\RotationPlanner;
use App\Models\Assignment;
use App\Models\Kid;
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

        return view('app.today', [
            'kid' => $kid,
            'date' => $date,
            'isWeekday' => $this->planner->isWeekday($date),
            'assignments' => $assignments,
            'isGrounded' => $isGrounded,
        ]);
    }
}
