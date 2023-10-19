<?php

use App\Models\Activity;
use App\Models\AssignedSubject;
use App\Models\AssignedSubjectsClass;
use App\Models\Exam;
use App\Models\PerformanceLevel;
use App\Models\School;
use App\Models\SchoolAdmins;
use App\Models\Subjects;
use App\Models\SummativePerformnceLevel;
use App\Models\Term;
use Illuminate\Support\Facades\Auth;

function getSchoolSettings()
{
    return School::where('id', Auth::user()->school_id)->first();
}

function getSchoolAdmins($id = null)
{
    if ($id === null) {
        $id = Auth::user()->school_id;
    }
    $admins = SchoolAdmins::where('school_id', $id)->get();
    return $admins->pluck('admin_id')->toArray();
}

function getAssessmentDetails($learner_id, $obj)
{
    return $obj->where('learner_id', $learner_id)->first();
}

function initials($str)
{
    $ret = '';
    foreach (explode(' ', $str) as $word)
        $ret .= strtoupper($word[0]);
    return $ret;
}

function checkSummetiveCriteria($points, $remark = false, $comment = 0)
{
    $admins = getSchoolAdmins();
    $level = SummativePerformnceLevel::when(in_array(Auth::user()->role, ['admin', 'teacher']), function ($q) use ($admins) {
        return $q->whereIn('created_by', $admins);
    })
        ->where('min_point', '<=', $points)
        ->where('max_point', '>=', $points)
        ->first();

    if (!empty($level)) {
        if (!$remark) {
            if ($comment) {
                return $level['comment_' . $comment];
            }
            return $level->title;
        } else {
            if (!$comment) {
                return $level->teacher_remark;
            }
            return $level->comment_ . $comment;
        }
    }

    return '';
}

function checkPointsCriteria($points, $remark = false, $comment = false)
{
    $levels = PerformanceLevel::get();
    $points = round($points, 1);

    $min = $levels->min('min_point');
    $max = $levels->max('max_point');

    if ($points < $min) {
        $level = $levels->where('min_point', $min)->first();
    } else if ($points > $max) {
        $level = $levels->where('max_point', $max)->first();
    } else {
        $level = $levels->where('min_point', '<=', $points)
            ->where('max_point', '>=', $points)->first();
    }

    if (!empty($level)) {
        if (!$remark) {
            if ($comment) {
                return $level['comment_' . $comment];
            }
            return $level->title;
        } else {
            if (!$comment) {
                return $level->teacher_remark;
            }
            return $level->comment_ . $comment;
        }
    }

    return '';
}

function getSchoolSubjects($all = true, $id = null)
{
    $data = [];
    $teacher_assigned_subjects = [];
    if (Auth::user()->role === 'teacher') {
        $teacher_assigned_subjects = AssignedSubject::where('teacher_id', Auth::id())->get();
        if (count($teacher_assigned_subjects)) {
            $teacher_assigned_subjects = $teacher_assigned_subjects->pluck('subject_id')->toArray();
        }
    }
    $assigned_subjects = AssignedSubjectsClass::where('school_id', Auth::user()->school_id)
        ->when($id, function ($q) use ($id) {
            return $q->where('class_id', $id);
        })
        ->when(Auth::user()->role === 'teacher', function ($q) use ($teacher_assigned_subjects) {
            return $q->whereIn('subject_id', $teacher_assigned_subjects);
        })
        ->get();

    if (!empty($assigned_subjects)) {
        $assigned_subjects = $assigned_subjects->pluck('subject_id')->toArray();
        if (!$all) {
            $data = Subjects::with('school_class')
                ->whereIn('id', $assigned_subjects)
                ->orderBy('title')
                ->paginate(8);
        } else {
            $data = Subjects::with('school_class')
                ->whereIn('id', $assigned_subjects)
                ->orderBy('title')
                ->get();
        }
    }

    return $data;
}

function getSchools()
{
    if (Auth::user()->role === 'super_admin') {
        $schools = School::all();
    } else {
        $schools = School::where('id', Auth::user()->school_id)->get();
    }

    return $schools;
}

function checkFormativeTermLock($id)
{
    $term = Term::where('id', $id)->first();
    return !empty($term) ? $term->lock_term : 0;
}


function checkSummativeExamLock($id)
{
    $exam = Exam::where('id', $id)->first();
    return !empty($exam) ? $exam->exam_lock : 0;
}

function saveActivity($activity)
{
    Activity::create(['user_id' => Auth::id(), 'activity' => $activity]);
}

function getImage($image)
{
    if ($image) {
        return asset($image);
    } else {
        return "https://conference.onsemble.net/wp-content/themes/vanilla-onsemble22-new/img/placeholder-img.png";
    }
}
