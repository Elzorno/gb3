<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\InfractionEvent;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $kidId = (int)$request->session()->get('gb2_kid_id', 0);
        if ($kidId <= 0) {
            return redirect()->route('kid.login')->with('status', 'Please log in first.');
        }

        $status = (string)$request->query('status', '');
        $kind = (string)$request->query('kind', '');
        $perPage = max(5, min(50, (int)$request->query('per_page', 10)));

        $q = Submission::query()
            ->with(['slot'])
            ->where('kid_id', $kidId)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $q->where('status', $status);
        }
        if (in_array($kind, ['base', 'bonus'], true)) {
            $q->where('kind', $kind);
        }

        $rows = $q->paginate($perPage)->withQueryString();

        $infractions = InfractionEvent::query()
            ->with(['definition'])
            ->where('kid_id', $kidId)
            ->orderByDesc('ts')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return view('history.index', [
            'rows' => $rows,
            'infractions' => $infractions,
            'status' => $status,
            'kind' => $kind,
            'perPage' => $perPage,
        ]);
    }
}
