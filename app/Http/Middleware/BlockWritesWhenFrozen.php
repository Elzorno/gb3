<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockWritesWhenFrozen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isWriteMethod($request)) {
            return $next($request);
        }

        if (!$this->isFrozen()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'maintenance',
                'message' => 'Write operations are temporarily frozen for maintenance.',
            ], 503)->header('Retry-After', '300');
        }

        return response('Write operations are temporarily frozen for maintenance.', 503)
            ->header('Retry-After', '300');
    }

    private function isFrozen(): bool
    {
        return is_file($this->flagPath());
    }

    private function flagPath(): string
    {
        return storage_path('framework/gb3_write_freeze.flag');
    }

    private function isWriteMethod(Request $request): bool
    {
        return in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }
}
