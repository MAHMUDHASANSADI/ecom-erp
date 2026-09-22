<?php

namespace Modules\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'activity_log';

    protected $fillable = [
        'subject_type',
        'subject_id',
        'causer_id',
        'description',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Log an activity entry.
     *
     * @param  array<string, mixed>|null  $properties
     */
    public static function record(
        object|string $subject,
        string $description,
        ?int $causerId = null,
        ?array $properties = null
    ): self {
        $subjectType = is_object($subject) ? get_class($subject) : $subject;
        $subjectId = is_object($subject) ? $subject->getKey() : 0;

        return static::create([
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'causer_id' => $causerId ?? auth()->id(),
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
