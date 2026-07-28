<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_volume_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('run_id', 100)->unique();
            $table->string('status', 20)->index();
            $table->unsignedBigInteger('target_bytes');
            $table->unsignedBigInteger('bytes_per_second');
            $table->unsignedInteger('payload_bytes');
            $table->unsignedBigInteger('progress_bytes');
            $table->unsignedBigInteger('written_bytes')->default(0);
            $table->unsignedBigInteger('lines')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('cancel_requested_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_volume_runs');
    }
};
