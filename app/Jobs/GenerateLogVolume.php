<?php

namespace App\Jobs;

use App\Models\LogVolumeRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Throwable;

class GenerateLogVolume implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    private const CHUNK_SECONDS = 10;
    private const CANCEL_CHECK_MICROSECONDS = 500_000;
    private const MAX_LINES_PER_SECOND = 12;

    public function __construct(public int $runId)
    {
    }

    public function handle(): void
    {
        $run = LogVolumeRun::find($this->runId);
        if (! $run || ! $run->isActive()) {
            return;
        }

        if ($run->cancel_requested_at) {
            $this->finish($run, 'cancelled');
            return;
        }

        if ($run->status === 'queued') {
            $run->forceFill([
                'status' => 'running',
                'started_at' => now(),
            ])->save();
        }

        $chunkStartedAt = hrtime(true);
        $lastCancelCheckAt = $chunkStartedAt;
        $chunkWritten = 0;
        $chunkLines = 0;
        $nextProgressAt = (intdiv($run->written_bytes, $run->progress_bytes) + 1) * $run->progress_bytes;

        while ($run->written_bytes < $run->target_bytes) {
            $now = hrtime(true);
            if (($now - $chunkStartedAt) >= self::CHUNK_SECONDS * 1_000_000_000) {
                break;
            }

            if (($now - $lastCancelCheckAt) >= self::CANCEL_CHECK_MICROSECONDS * 1_000) {
                if ($this->cancellationRequested($run)) {
                    $this->finish($run, 'cancelled');
                    return;
                }
                $lastCancelCheckAt = $now;
            }

            $line = sprintf(
                "log-volume-test run_id=%s sequence=%d emitted_at=%s payload=%s\n",
                $run->run_id,
                $run->lines,
                gmdate('Y-m-d\TH:i:s\Z'),
                base64_encode(random_bytes($run->payload_bytes)),
            );

            $this->writeAll(STDERR, $line);
            $length = strlen($line);
            $run->written_bytes += $length;
            ++$run->lines;
            $chunkWritten += $length;
            ++$chunkLines;

            if ($run->written_bytes >= $nextProgressAt) {
                $run->save();
                $this->writeProgress($run);
                $nextProgressAt = (intdiv($run->written_bytes, $run->progress_bytes) + 1) * $run->progress_bytes;
            }

            $expectedSeconds = max(
                $chunkWritten / $run->bytes_per_second,
                $chunkLines / self::MAX_LINES_PER_SECOND,
            );
            $actualSeconds = (hrtime(true) - $chunkStartedAt) / 1_000_000_000;
            $delay = (int) (($expectedSeconds - $actualSeconds) * 1_000_000);
            if ($delay > 0) {
                usleep(min($delay, 1_000_000));
            }
        }

        $run->save();

        if ($run->written_bytes >= $run->target_bytes) {
            $this->finish($run, 'complete');
            return;
        }

        self::dispatch($run->id);
    }

    protected function cancellationRequested(LogVolumeRun $run): bool
    {
        return $run->newQuery()
            ->whereKey($run->getKey())
            ->whereNotNull('cancel_requested_at')
            ->exists();
    }

    public function failed(?Throwable $exception): void
    {
        LogVolumeRun::whereKey($this->runId)
            ->whereIn('status', ['queued', 'running', 'cancelling'])
            ->update([
                'status' => 'failed',
                'error' => $exception?->getMessage() ?? 'The worker job failed.',
                'finished_at' => now(),
            ]);
    }

    private function finish(LogVolumeRun $run, string $status): void
    {
        $run->forceFill([
            'status' => $status,
            'finished_at' => now(),
        ])->save();

        $elapsed = max($run->started_at?->diffInMilliseconds(now()) / 1000, 0.001);
        $this->writeAll(STDERR, sprintf(
            "log-volume-finished run_id=%s status=%s bytes=%d lines=%d elapsed_seconds=%.3f average_bytes_per_second=%.0f\n",
            $run->run_id,
            $status,
            $run->written_bytes,
            $run->lines,
            $elapsed,
            $run->written_bytes / $elapsed,
        ));
    }

    private function writeProgress(LogVolumeRun $run): void
    {
        $elapsed = max($run->started_at?->diffInMilliseconds(now()) / 1000, 0.001);
        $this->writeAll(STDERR, sprintf(
            "log-volume-progress run_id=%s bytes=%d target_bytes=%d elapsed_seconds=%.3f average_bytes_per_second=%.0f\n",
            $run->run_id,
            $run->written_bytes,
            $run->target_bytes,
            $elapsed,
            $run->written_bytes / $elapsed,
        ));
    }

    /** @param resource $stream */
    private function writeAll($stream, string $contents): void
    {
        $length = strlen($contents);
        $offset = 0;
        while ($offset < $length) {
            $written = fwrite($stream, substr($contents, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('Unable to write generated log data.');
            }
            $offset += $written;
        }
    }
}
