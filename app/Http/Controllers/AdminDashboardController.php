<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Kid;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin dashboard with overview stats.
     */
    public function index(Request $request): View
    {
        // Get all kids with their current status
        $kids = Kid::all();
        
        // Get pending reviews count
        $pendingReviews = Submission::where('status', 'pending')->count();
        
        // Get today's activity
        $today = now()->toDateString();
        $todaySubmissions = Submission::whereDate('created_at', $today)->count();
        
        // Get kids needing attention (grounded or with pending infractions)
        $kidsNeedingAttention = $kids->filter(function ($kid) {
            return optional($kid)->is_grounded;
        })->count();

        // Get family name from settings
        $familyName = DB::table('settings')
            ->where('key', 'family_name')
            ->value('value') ?? 'Family';

        return view('admin.dashboard', [
            'kids' => $kids,
            'pendingReviews' => $pendingReviews,
            'todaySubmissions' => $todaySubmissions,
            'kidsNeedingAttention' => $kidsNeedingAttention,
            'familyName' => $familyName,
        ]);
    }
}
