<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\InfractionEvent;
use App\Models\Kid;
use App\Models\Submission;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $kidId = (int)$request->session()->get('gb2_kid_id', 0);
        if ($kidId <= 0) {
            return redirect()->route('app.login')->with('error', 'Please log in first.');
        }

        $kid = Kid::find($kidId);
        $filter = (string)$request->query('filter', 'all'); // all, chores, bonuses
        $perPage = 15;

        $q = Submission::query()
            ->with(['slot', 'bonusInstance.definition'])
            ->where('kid_id', $kidId)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if ($filter === 'chores') {
            $q->where('kind', 'base');
        } elseif ($filter === 'bonuses') {
            $q->where('kind', 'bonus');
        }

        $submissions = $q->paginate($perPage)->withQueryString();

        // Stats for this week
        $weekStart = CarbonImmutable::now()->startOfWeek(CarbonImmutable::MONDAY)->format('Y-m-d');
        $weekStats = [
            'chores_done' => Submission::where('kid_id', $kidId)
                ->where('kind', 'base')
                ->where('status', 'approved')
                ->whereDate('day', '>=', $weekStart)
                ->count(),
            'bonuses_done' => Submission::where('kid_id', $kidId)
                ->where('kind', 'bonus')
                ->where('status', 'approved')
                ->where('week_start', $weekStart)
                ->count(),
        ];

        return view('app.history', [
            'kid' => $kid,
            'submissions' => $submissions,
            'filter' => $filter,
            'weekStats' => $weekStats,
        ]);
    }
}
