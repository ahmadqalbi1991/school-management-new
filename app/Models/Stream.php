<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stream extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'slug', 'class_id', 'school_id'];

    public function school_class() {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function assigned_subjects(): HasMany
    {
        return $this->hasMany(AssignedSubject::class, 'stream_id', 'id');
    }

    public function learner_subjects(): HasMany
    {
        return $this->hasMany(LearnerSubject::class, 'stream_id', 'id');
    }

    public function student_assessments(): HasMany
    {
        return $this->hasMany(StudentAssessment::class, 'stream_id', 'id');
    }

    public function summative_assessments(): HasMany
    {
        return $this->hasMany(SummativeAssessment::class, 'stream_id', 'id');
    }

    public function teachers_management(): HasMany
    {
        return $this->hasMany(TeacherManagement::class, 'stream_id', 'id');
    }

    public function learners(): HasMany
    {
        return $this->hasMany(User::class, 'stream_id', 'id');
    }
}
