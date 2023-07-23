<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedSubjectsClass extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['class_id', 'school_id', 'subject_id'];
}
