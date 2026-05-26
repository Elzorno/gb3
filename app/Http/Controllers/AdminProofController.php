<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AdminProofController extends Controller
{
    public function show(Submission $submission): Response
    {
        abort_unless($submission->proof_path, 404);

        [$disk, $path] = $this->resolveDiskAndPath((string) $submission->proof_path);

        abort_unless(Storage::disk($disk)->exists($path), 404);

        try {
            $stream = Storage::disk($disk)->readStream($path);
        } catch (FileNotFoundException) {
            abort(404);
        }

        abort_unless($stream !== false, 404);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => Storage::disk($disk)->mimeType($path) ?? 'application/octet-stream',
            'Content-Length' => (string) Storage::disk($disk)->size($path),
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    /**
     * Support new private uploads and legacy public-disk proof files.
     *
     * @return array{0:string,1:string}
     */
    private function resolveDiskAndPath(string $proofPath): array
    {
        if (Storage::disk('local')->exists($proofPath)) {
            return ['local', $proofPath];
        }

        return ['public', $proofPath];
    }
}
