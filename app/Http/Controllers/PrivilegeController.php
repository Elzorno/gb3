<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Kid;
use App\Models\Privilege;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivilegeController extends Controller
{
    /**
     * Display privilege status for all kids
     */
    public function index(): View
    {
        $kids = Kid::with('privileges')
            ->orderBy('sort_order')
            ->get();

        // Auto-unlock any expired locks
        foreach ($kids as $kid) {
            if ($kid->privileges) {
                $this->autoUnlockExpired($kid->privileges);
            }
        }

        return view('admin.privileges.index', [
            'kids' => $kids,
        ]);
    }

    /**
     * Show detailed privilege management for one kid
     */
    public function show(Kid $kid): View
    {
        $privilege = $kid->privileges;
        
        if (!$privilege) {
            // Ensure privilege row exists
            $privilege = Privilege::create(['kid_id' => $kid->id]);
            $kid->setRelation('privileges', $privilege);
        }

        $this->autoUnlockExpired($privilege);

        return view('admin.privileges.show', [
            'kid' => $kid,
            'privilege' => $privilege,
        ]);
    }

    /**
     * Toggle lock status for a privilege type
     */
    public function toggleLock(Request $request, Kid $kid): RedirectResponse
    {
        $v = $request->validate([
            'type' => ['required', 'in:phone,games,other'],
            'duration' => ['nullable', 'integer', 'min:0'], // minutes, 0 = indefinite
        ]);

        $type = $v['type'];
        $duration = isset($v['duration']) ? (int)$v['duration'] : null;
        $lockedField = "{$type}_locked";
        $untilField = "{$type}_locked_until";

        $privilege = $kid->privileges ?? Privilege::create(['kid_id' => $kid->id]);
        
        // Toggle: if currently locked, unlock; if unlocked, lock
        if ($privilege->$lockedField) {
            // Unlock
            $privilege->$lockedField = false;
            $privilege->$untilField = null;
        } else {
            // Lock
            $privilege->$lockedField = true;
            if ($duration && $duration > 0) {
                $privilege->$untilField = Carbon::now()->addMinutes($duration);
            } else {
                $privilege->$untilField = null; // indefinite
            }
        }

        $privilege->updated_at = now();
        $privilege->save();

        $action = $privilege->$lockedField ? 'locked' : 'unlocked';
        return redirect()->back()->with('status', ucfirst($type) . " privileges {$action} for {$kid->display_name}.");
    }

    /**
     * Set time bank for a kid
     */
    public function updateBank(Request $request, Kid $kid): RedirectResponse
    {
        $v = $request->validate([
            'bank_phone_min' => ['required', 'integer', 'min:0'],
            'bank_games_min' => ['required', 'integer', 'min:0'],
            'bank_other_min' => ['required', 'integer', 'min:0'],
        ]);

        $privilege = $kid->privileges ?? Privilege::create(['kid_id' => $kid->id]);
        
        $privilege->bank_phone_min = (int)$v['bank_phone_min'];
        $privilege->bank_games_min = (int)$v['bank_games_min'];
        $privilege->bank_other_min = (int)$v['bank_other_min'];
        $privilege->updated_at = now();
        $privilege->save();

        return redirect()->back()->with('status', "Time banks updated for {$kid->display_name}.");
    }

    /**
     * Set grounding with duration
     */
    public function setGrounding(Request $request, Kid $kid): RedirectResponse
    {
        $v = $request->validate([
            'types' => ['required', 'array', 'min:1'],
            'types.*' => ['in:phone,games,other'],
            'duration_days' => ['nullable', 'integer', 'min:0', 'max:365'],
        ]);

        $types = $v['types'];
        $days = isset($v['duration_days']) ? (int)$v['duration_days'] : 0;

        $privilege = $kid->privileges ?? Privilege::create(['kid_id' => $kid->id]);
        
        foreach ($types as $type) {
            $lockedField = "{$type}_locked";
            $untilField = "{$type}_locked_until";
            
            $privilege->$lockedField = true;
            if ($days > 0) {
                $privilege->$untilField = Carbon::now()->addDays($days);
            } else {
                $privilege->$untilField = null; // indefinite
            }
        }

        $privilege->updated_at = now();
        $privilege->save();

        $typeList = implode(', ', array_map('ucfirst', $types));
        $durationText = $days > 0 ? "for {$days} day(s)" : "indefinitely";
        
        return redirect()->back()->with('status', "{$kid->display_name} is now grounded from {$typeList} {$durationText}.");
    }

    /**
     * Lift all grounding for a kid
     */
    public function liftGrounding(Kid $kid): RedirectResponse
    {
        $privilege = $kid->privileges;
        
        if ($privilege) {
            $privilege->phone_locked = false;
            $privilege->games_locked = false;
            $privilege->other_locked = false;
            $privilege->phone_locked_until = null;
            $privilege->games_locked_until = null;
            $privilege->other_locked_until = null;
            $privilege->updated_at = now();
            $privilege->save();
        }

        return redirect()->back()->with('status', "All grounding lifted for {$kid->display_name}.");
    }

    /**
     * Auto-unlock expired locks
     */
    private function autoUnlockExpired(Privilege $privilege): void
    {
        $now = Carbon::now();
        $changed = false;

        foreach (['phone', 'games', 'other'] as $type) {
            $lockedField = "{$type}_locked";
            $untilField = "{$type}_locked_until";

            if ($privilege->$lockedField && $privilege->$untilField && $privilege->$untilField->lte($now)) {
                $privilege->$lockedField = false;
                $privilege->$untilField = null;
                $changed = true;
            }
        }

        if ($changed) {
            $privilege->updated_at = now();
            $privilege->save();
        }
    }
}
