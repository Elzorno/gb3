<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminSessionService
{
    /**
     * Check if admin is currently logged in.
     */
    public function isLoggedIn(Request $request): bool
    {
        return $request->session()->has('gb2_admin_logged_in')
            && $request->session()->get('gb2_admin_logged_in') === true;
    }

    /**
     * Verify admin password against the stored hash.
     */
    public function verifyPassword(string $password, string $storedHash): bool
    {
        // Support both bcrypt (preferred) and legacy MD5 hashes
        if (strlen($storedHash) === 32 && ctype_xdigit($storedHash)) {
            // Legacy MD5 hash
            return md5($password) === $storedHash;
        }

        // Modern bcrypt/password_hash
        return Hash::check($password, $storedHash);
    }

    /**
     * Log in the admin.
     */
    public function login(Request $request): void
    {
        // Regenerate session ID on login for security
        $request->session()->regenerate();
        
        $request->session()->put('gb2_admin_logged_in', true);
        $request->session()->put('gb2_admin_logged_in_at', now()->toIso8601String());
    }

    /**
     * Log out the admin.
     */
    public function logout(Request $request): void
    {
        $request->session()->forget([
            'gb2_admin_logged_in',
            'gb2_admin_logged_in_at',
        ]);
        
        // Regenerate session ID on logout for security
        $request->session()->regenerate(true);
    }

    /**
     * Get admin password hash from config/database.
     * Returns null if no password is set (initial setup needed).
     */
    public function getStoredPasswordHash(): ?string
    {
        // First check if we have config in database
        $hash = $this->getPasswordFromDatabase();
        if ($hash !== null) {
            return $hash;
        }

        // Fall back to config file
        return config('gb.admin_password_hash');
    }

    /**
     * Check if initial setup is needed (no admin password set).
     */
    public function needsSetup(): bool
    {
        return $this->getStoredPasswordHash() === null;
    }

    /**
     * Get rate limit key for admin login attempts.
     */
    public function rateLimitKey(Request $request): string
    {
        $ip = (string) $request->ip();
        return 'admin_login_' . hash('sha256', $ip);
    }

    /**
     * Get password hash from database settings table.
     */
    private function getPasswordFromDatabase(): ?string
    {
        try {
            $result = \DB::table('settings')
                ->where('key', 'admin_password_hash')
                ->value('value');
            
            return $result ? (string) $result : null;
        } catch (\Exception $e) {
            // Table may not exist yet
            return null;
        }
    }

    /**
     * Set admin password (stores bcrypt hash in database).
     */
    public function setPassword(string $password): void
    {
        $hash = Hash::make($password);

        \DB::table('settings')->updateOrInsert(
            ['key' => 'admin_password_hash'],
            ['value' => $hash, 'updated_at' => now()]
        );
    }
}
