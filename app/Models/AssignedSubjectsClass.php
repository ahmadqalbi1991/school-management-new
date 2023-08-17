<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignedSubjectsClass extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['class_id', 'school_id', 'subject_id'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subjects::class, 'subject_id', 'id');
    }

    public function school_class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id');
    }
}
