<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LearnerSubject extends Model
{
    use HasFactory;
    protected $fillable = ['class_id', 'stream_id', 'subject_id', 'learner_id', 'all_students'];
    public $timestamps = false;

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id');
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subjects::class, 'subject_id', 'id');
    }

    public function learner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'learner_id', 'id');
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(SummativeAssessment::class, 'subject_id', 'subject_id');
    }
}
