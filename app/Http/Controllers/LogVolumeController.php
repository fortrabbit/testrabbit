<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateLogVolume;
use App\Models\LogVolumeRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class LogVolumeController extends Controller
{
    public function index()
    {
        return view('log-volume');
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'run' => $this->serialize(LogVolumeRun::latest('id')->first()),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $values = $request->validate([
            'target_size' => ['required', 'string', 'max:32'],
            'bytes_per_second' => ['required', 'integer', 'min:1', 'max:100000000'],
            'payload_bytes' => ['required', 'integer', 'min:48', 'max:65536'],
            'run_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/'],
        ]);

        $targetBytes = $this->parseBytes($values['target_size'], 10_000_000_000);
        $progressBytes = min(100_000_000, $targetBytes);

        $run = DB::transaction(function () use ($values, $targetBytes, $progressBytes) {
            $active = LogVolumeRun::whereIn('status', ['queued', 'running', 'cancelling'])
                ->lockForUpdate()
                ->first();

            if ($active) {
                throw ValidationException::withMessages([
                    'run' => 'A log-volume run is already active.',
                ]);
            }

            return LogVolumeRun::create([
                'run_id' => $values['run_id'] ?: bin2hex(random_bytes(8)),
                'status' => 'queued',
                'target_bytes' => $targetBytes,
                'bytes_per_second' => $values['bytes_per_second'],
                'payload_bytes' => $values['payload_bytes'],
                'progress_bytes' => $progressBytes,
                'written_bytes' => 0,
                'lines' => 0,
            ]);
        });

        try {
            GenerateLogVolume::dispatch($run->id);
        } catch (Throwable $exception) {
            $run->forceFill([
                'status' => 'failed',
                'error' => 'Unable to queue the worker job: '.$exception->getMessage(),
                'finished_at' => now(),
            ])->save();

            return response()->json([
                'message' => 'Unable to queue the log-volume run.',
                'run' => $this->serialize($run),
            ], 500);
        }

        return response()->json(['run' => $this->serialize($run)], 202);
    }

    public function stop(LogVolumeRun $run): JsonResponse
    {
        if ($run->isActive()) {
            $run->forceFill([
                'status' => 'cancelled',
                'cancel_requested_at' => $run->cancel_requested_at ?? now(),
                'finished_at' => now(),
            ])->save();
        }

        return response()->json(['run' => $this->serialize($run->fresh())]);
    }

    private function parseBytes(string $value, int $maximum): int
    {
        if (! preg_match('/^(\d+(?:\.\d+)?)\s*(b|kb|mb|gb|kib|mib|gib)?$/i', trim($value), $matches)) {
            throw ValidationException::withMessages([
                'size' => 'Use a size such as 200mb, 1gb, or 2gib.',
            ]);
        }

        $multiplier = match (strtolower($matches[2] ?? 'b')) {
            'b' => 1,
            'kb' => 1_000,
            'mb' => 1_000_000,
            'gb' => 1_000_000_000,
            'kib' => 1_024,
            'mib' => 1_048_576,
            'gib' => 1_073_741_824,
        };
        $bytes = (int) round((float) $matches[1] * $multiplier);

        if ($bytes < 1 || $bytes > $maximum) {
            throw ValidationException::withMessages([
                'size' => 'The requested size must be between 1 byte and '.number_format($maximum).' bytes.',
            ]);
        }

        return $bytes;
    }

    private function serialize(?LogVolumeRun $run): ?array
    {
        if (! $run) {
            return null;
        }

        return [
            'id' => $run->id,
            'run_id' => $run->run_id,
            'status' => $run->status,
            'target_bytes' => $run->target_bytes,
            'bytes_per_second' => $run->bytes_per_second,
            'payload_bytes' => $run->payload_bytes,
            'progress_bytes' => $run->progress_bytes,
            'written_bytes' => $run->written_bytes,
            'lines' => $run->lines,
            'error' => $run->error,
            'cancel_requested_at' => $run->cancel_requested_at?->toIso8601String(),
            'started_at' => $run->started_at?->toIso8601String(),
            'finished_at' => $run->finished_at?->toIso8601String(),
            'created_at' => $run->created_at?->toIso8601String(),
        ];
    }
}
