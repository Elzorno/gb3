<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\InfractionEvent;
use App\Models\Kid;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $kids = Kid::with('privileges')->orderBy('sort_order')->get();

        $pendingReviews = Submission::where('status', 'pending')->count();

        $today = now()->toDateString();
        $todaySubmissions = Submission::whereDate('created_at', $today)->count();

        $kidsNeedingAttention = $kids->filter(fn($kid) => $kid->is_grounded)->count();

        // Recent pending submissions for quick inline review
        $pendingItems = Submission::with(['kid', 'slot', 'bonusInstance.definition'])
            ->where('status', 'pending')
            ->orderBy('submitted_at')
            ->limit(5)
            ->get();

        // Infraction events needing review (review_on <= today, not yet reviewed)
        $dueInfractionReviews = InfractionEvent::with(['kid', 'definition'])
            ->whereNotNull('review_on')
            ->whereNull('reviewed_at')
            ->where('review_on', '<=', $today)
            ->orderBy('review_on')
            ->limit(5)
            ->get();

        // Active locks detail per kid
        $activeLocks = $kids->filter(fn($kid) => $kid->is_grounded)->map(function ($kid) {
            $priv = $kid->privileges;
            $locks = [];
            foreach (['phone', 'games', 'other'] as $type) {
                if ($priv->{$type . '_locked'}) {
                    $until = $priv->{$type . '_locked_until'};
                    $locks[] = [
                        'type' => $type,
                        'until' => $until,
                        'label' => ucfirst($type),
                    ];
                }
            }
            return ['kid' => $kid, 'locks' => $locks];
        })->values();

        // Check if writes are frozen
        $isFrozen = is_file(storage_path('framework/gb3_write_freeze.flag'));

        $familyName = DB::table('settings')
            ->where('key', 'family_name')
            ->value('value') ?? 'Family';

        return view('admin.dashboard', [
            'kids' => $kids,
            'pendingReviews' => $pendingReviews,
            'todaySubmissions' => $todaySubmissions,
            'kidsNeedingAttention' => $kidsNeedingAttention,
            'pendingItems' => $pendingItems,
            'dueInfractionReviews' => $dueInfractionReviews,
            'activeLocks' => $activeLocks,
            'isFrozen' => $isFrozen,
            'familyName' => $familyName,
        ]);
    }
}
