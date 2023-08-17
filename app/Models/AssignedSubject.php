<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignedSubject extends Model
{
    use HasFactory;
    protected $table = 'assigned_subjects';
    public $timestamps = false;
    protected $fillable = ['teacher_id', 'subject_id'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subjects::class, 'subject_id', 'id')->with('school_class');
    }

    public function assigned_class(): BelongsTo
    {
        return $this->belongsTo(AssignedSubjectsClass::class, 'subject_id', 'subject_id')->with('school_class');
    }
}
