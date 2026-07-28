<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogVolumeRun extends Model
{
    protected $guarded = [];

    protected $casts = [
        'target_bytes' => 'integer',
        'bytes_per_second' => 'integer',
        'payload_bytes' => 'integer',
        'progress_bytes' => 'integer',
        'written_bytes' => 'integer',
        'lines' => 'integer',
        'cancel_requested_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return in_array($this->status, ['queued', 'running', 'cancelling'], true);
    }
}
