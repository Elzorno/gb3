<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Rotation\RotationPlanner;
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
        $date = CarbonImmutable::today('UTC');
        $assignments = $this->planner->ensureAssignmentsForDay($date);

        return view('rotation.today', [
            'date' => $date->format('Y-m-d'),
            'isWeekday' => $this->planner->isWeekday($date),
            'assignments' => $assignments,
        ]);
    }
}
