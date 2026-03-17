<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Rotation\RotationPlanner;
use App\Models\Assignment;
use App\Models\ChoreSlot;
use App\Models\Kid;
use App\Models\RotationRule;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RulesController extends Controller
{
    public function __construct(
        private readonly RotationPlanner $planner,
    ) {
    }

    /**
     * Display the weekly schedule grid showing all kids' chore assignments
     */
    public function index(Request $request): View
    {
        $kidId = (int) $request->session()->get('gb2_kid_id');
        
        // Get the current week (Monday through Friday)
        $today = CarbonImmutable::today(config('app.timezone'));
        $dayOfWeek = $today->dayOfWeekIso; // 1=Mon, 7=Sun
        
        // Calculate start of week (Monday)
        $weekStart = $today->subDays($dayOfWeek - 1);
        
        // Generate week days (Mon-Fri)
        $weekDays = [];
        for ($i = 0; $i < 5; $i++) {
            $weekDays[] = $weekStart->addDays($i);
        }
        
        // Get all kids in the rotation
        // Legacy data stores names in kids_json, new format uses IDs
        $rule = RotationRule::first();
        $kids = collect();
        
        if ($rule) {
            $kidsJson = json_decode($rule->kids_json, true) ?: [];
            
            if (!empty($kidsJson)) {
                // Check if first item is numeric (ID) or string (name)
                $firstItem = $kidsJson[0] ?? null;
                if (is_numeric($firstItem)) {
                    // New format: IDs
                    $kidIds = array_map('intval', $kidsJson);
                    $kids = Kid::whereIn('id', $kidIds)->orderBy('sort_order')->get();
                } else {
                    // Legacy format: names - look up by display_name and preserve order
                    $kidsFromDb = Kid::whereIn('display_name', $kidsJson)->get()->keyBy('display_name');
                    $kids = collect($kidsJson)
                        ->map(fn($name) => $kidsFromDb->get($name))
                        ->filter()
                        ->values();
                }
            }
        }
        
        // Fallback: if no kids from rule, show all kids
        if ($kids->isEmpty()) {
            $kids = Kid::orderBy('sort_order')->get();
        }
        
        // Get all active chore slots
        $slots = ChoreSlot::where('active', true)->orderBy('sort_order')->get();
        
        // Ensure assignments exist for the week
        foreach ($weekDays as $day) {
            if ($this->planner->isWeekday($day)) {
                $this->planner->ensureAssignmentsForDay($day);
            }
        }
        
        // Build the schedule grid: [day][kid_id] => assignment
        $schedule = [];
        foreach ($weekDays as $day) {
            $dayStr = $day->format('Y-m-d');
            $schedule[$dayStr] = [];
            
            $dayAssignments = Assignment::with('slot')
                ->where('day', $dayStr)
                ->get()
                ->keyBy('kid_id');
            
            foreach ($kids as $kid) {
                $schedule[$dayStr][$kid->id] = $dayAssignments->get($kid->id);
            }
        }
        
        return view('app.rules', [
            'currentKidId' => $kidId,
            'kids' => $kids,
            'weekDays' => $weekDays,
            'schedule' => $schedule,
            'today' => $today->format('Y-m-d'),
        ]);
    }
}
