<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Submission\SubmissionService;
use App\Models\Assignment;
use App\Models\Kid;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function __construct(
        private readonly SubmissionService $service,
    ) {
    }

    public function create(Request $request): View
    {
        $kidId = (int)($request->session()->get('gb2_kid_id', 0));
        $kid = $kidId > 0 ? Kid::query()->find($kidId) : null;

        $today = CarbonImmutable::now(config('app.timezone'));
        $todayYmd = $today->format('Y-m-d');

        // Get today's assignments that can still be submitted
        $assignments = collect();
        if ($kid) {
            $assignments = Assignment::query()
                ->with('slot')
                ->where('kid_id', $kid->id)
                ->whereDate('day', $todayYmd)
                ->whereNotIn('status', ['approved', 'completed'])
                ->orderBy('slot_id')
                ->get();
        }

        // Check if a specific slot was requested
        $selectedSlot = null;
        $selectedAssignment = null;
        $slotId = (int)$request->query('slot', 0);
        if ($slotId > 0) {
            $selectedAssignment = $assignments->firstWhere('slot_id', $slotId);
            $selectedSlot = $selectedAssignment?->slot;
        }

        return view('app.submit', [
            'kid' => $kid,
            'today' => $today,
            'assignments' => $assignments,
            'selectedSlot' => $selectedSlot,
            'selectedAssignment' => $selectedAssignment,
        ]);
    }

    public function storeBase(Request $request): RedirectResponse
    {
        $kidId = (int)($request->session()->get('gb2_kid_id', 0));
        if ($kidId <= 0) {
            return redirect()->route('app.login')->with('status', 'Please log in first.');
        }

        $v = $request->validate([
            'slot_id' => ['required', 'integer', 'min:1'],
            'day' => ['required', 'date_format:Y-m-d'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp,heic,heif', 'max:20480'],
        ], [
            'photo.required' => 'Photo upload failed — the image may be too large. Try taking the photo in normal mode instead of 48MP.',
            'photo.max' => 'Photo is too large (max 20 MB). Try taking the photo in normal mode.',
        ]);

        // Handle file upload
        $file = $request->file('photo');
        $ext = in_array($file->getClientOriginalExtension(), ['jpg','jpeg','png','gif','webp','heic','heif'], true)
            ? $file->getClientOriginalExtension()
            : 'jpg';
        $filename = sprintf(
            '%s_%d_%d.%s',
            $v['day'],
            $kidId,
            $v['slot_id'],
            $ext
        );
        
        // Store in public uploads directory
        $path = $file->storeAs('uploads/proofs', $filename, 'public');

        $this->service->submitBase(
            $kidId,
            (int)$v['slot_id'],
            (string)$v['day'],
            $path,
        );

        return redirect()->route('app.today')->with('status', 'Great job! Your proof has been submitted for review.');
    }
}
