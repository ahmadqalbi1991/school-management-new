<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummativePerformnceLevel extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'detail',
        'min_point',
        'max_point',
        'created_by',
        'teacher_remark',
        'comment_1',
        'comment_2',
        'comment_3',
        'comment_4',
        'comment_5',
        'comment_6',
        'comment_7',
        'comment_8',
        'comment_9',
        'comment_10',
    ];
}
