<?php

namespace Tests\Feature;

use App\Jobs\GenerateLogVolume;
use App\Models\LogVolumeRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LogVolumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_control_page_is_available(): void
    {
        $this->get('/log-volume')->assertOk();
    }

    public function test_start_creates_a_run_and_dispatches_it(): void
    {
        Queue::fake();

        $response = $this->postJson('/log-volume/start', $this->validPayload());

        $response
            ->assertAccepted()
            ->assertJsonPath('run.status', 'queued')
            ->assertJsonPath('run.target_bytes', 2_000_000);

        $run = LogVolumeRun::sole();
        $this->assertSame('test-run', $run->run_id);
        Queue::assertPushed(
            GenerateLogVolume::class,
            fn (GenerateLogVolume $job): bool => $job->runId === $run->id,
        );
    }

    public function test_start_validates_sizes_and_worker_settings(): void
    {
        Queue::fake();

        $this->postJson('/log-volume/start', [
            'target_size' => 'lots',
            'bytes_per_second' => 0,
            'payload_bytes' => 12,
            'run_id' => 'spaces are invalid',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('log_volume_runs', 0);
        Queue::assertNothingPushed();
    }

    public function test_start_rejects_a_second_active_run(): void
    {
        Queue::fake();
        $this->createRun(['status' => 'running']);

        $this->postJson('/log-volume/start', $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('run');

        $this->assertDatabaseCount('log_volume_runs', 1);
        Queue::assertNothingPushed();
    }

    public function test_stop_immediately_cancels_an_active_run(): void
    {
        $run = $this->createRun(['status' => 'cancelling']);

        $this->postJson("/log-volume/{$run->id}/stop")
            ->assertOk()
            ->assertJsonPath('run.status', 'cancelled');

        $run->refresh();
        $this->assertSame('cancelled', $run->status);
        $this->assertNotNull($run->cancel_requested_at);
        $this->assertNotNull($run->finished_at);
    }

    public function test_status_returns_the_latest_run(): void
    {
        $this->createRun(['run_id' => 'older']);
        $latest = $this->createRun(['run_id' => 'latest', 'status' => 'complete']);

        $this->getJson('/log-volume/status')
            ->assertOk()
            ->assertJsonPath('run.id', $latest->id)
            ->assertJsonPath('run.run_id', 'latest')
            ->assertJsonPath('run.status', 'complete');
    }

    public function test_cancellation_check_preserves_unsaved_progress(): void
    {
        $run = $this->createRun(['status' => 'running']);
        $run->written_bytes = 400_000;
        $run->lines = 6;

        $job = new class($run->id) extends GenerateLogVolume
        {
            public function isCancellationRequested(LogVolumeRun $run): bool
            {
                return $this->cancellationRequested($run);
            }
        };

        $this->assertFalse($job->isCancellationRequested($run));
        $this->assertSame(400_000, $run->written_bytes);
        $this->assertSame(6, $run->lines);

        LogVolumeRun::whereKey($run->id)->update(['cancel_requested_at' => now()]);

        $this->assertTrue($job->isCancellationRequested($run));
        $this->assertSame(400_000, $run->written_bytes);
        $this->assertSame(6, $run->lines);
    }

    private function validPayload(): array
    {
        return [
            'target_size' => '2mb',
            'bytes_per_second' => 2_000_000,
            'payload_bytes' => 768,
            'run_id' => 'test-run',
        ];
    }

    private function createRun(array $attributes = []): LogVolumeRun
    {
        return LogVolumeRun::create(array_merge([
            'run_id' => bin2hex(random_bytes(8)),
            'status' => 'queued',
            'target_bytes' => 2_000_000,
            'bytes_per_second' => 2_000_000,
            'payload_bytes' => 768,
            'progress_bytes' => 1_000_000,
            'written_bytes' => 0,
            'lines' => 0,
        ], $attributes));
    }
}
