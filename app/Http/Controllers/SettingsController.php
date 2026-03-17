<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Auth\AdminSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private readonly AdminSessionService $adminSession,
    ) {
    }

    public function index(): View
    {
        $familyName = DB::table('settings')
            ->where('key', 'family_name')
            ->value('value') ?? '';

        return view('admin.settings', [
            'familyName' => $familyName,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $section = (string) $request->input('section', 'branding');

        if ($section === 'password') {
            return $this->updatePassword($request);
        }

        return $this->updateBranding($request);
    }

    private function updateBranding(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'family_name' => ['required', 'string', 'max:100'],
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => 'family_name'],
            ['value' => $validated['family_name'], 'updated_at' => now()],
        );

        return redirect()->route('admin.settings')
            ->with('success', 'Settings saved.');
    }

    private function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $storedHash = $this->adminSession->getStoredPasswordHash();

        if ($storedHash === null || !$this->adminSession->verifyPassword($validated['current_password'], $storedHash)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $this->adminSession->setPassword($validated['new_password']);

        return redirect()->route('admin.settings')
            ->with('success', 'Password updated.');
    }
}
