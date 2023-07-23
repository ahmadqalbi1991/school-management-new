<?php

namespace App\Http\Controllers;

use App\Jobs\EmailJob;
use App\Models\AssignedSubject;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subjects;
use App\Models\SummativeAssessment;
use App\Models\SummativePerformnceLevel;
use App\Models\TeacherManagement;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\Request;
use Auth, PDF;
use Yajra\DataTables\DataTables;

class SummativeAssessmentController extends Controller
{
    /**
     * @param $class_slug
     * @param $stream_slug
     * @param $subject_slug
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|void
     */
    public function index($class_slug = null, $stream_slug = null, $subject_slug = null)
    {
        try {
            if (!$stream_slug && !$class_slug) {
                $assigned_streams = TeacherManagement::where('teacher_id', Auth::id())->get();
                $assigned_ids = $assigned_streams->pluck('stream_id')->toArray();
                $streams = Stream::where('school_id', Auth::user()->school_id)
                    ->whereIn('id', $assigned_ids)
                    ->with('school_class')
                    ->get();

                return view('summative-assessments.streams', compact('streams'));
            }

            if ($class_slug && $stream_slug && !$subject_slug) {
                $subjects = getSchoolSubjects();

                return view('summative-assessments.subjects', compact('subjects', 'class_slug', 'stream_slug'));
            }

            if ($class_slug && $stream_slug && $subject_slug) {
                $class = SchoolClass::where([
                    'school_id' => Auth::user()->school_id,
                    'slug' => $class_slug
                ])->first();
                $stream = Stream::where([
                    'class_id' => $class->id,
                    'slug' => $stream_slug
                ])->first();
                $learners = User::where([
                    'stream_id' => $stream->id,
                    'school_id' => Auth::user()->school_id,
                    'status' => 'active',
                    'role' => 'learner'
                ])->get();
                $subject = Subjects::where('slug', $subject_slug)->first();
                $terms = Term::where('school_id', Auth::user()->school_id)->get();
                $admins = getSchoolAdmins();
                $min = SummativePerformnceLevel::whereIn('created_by', $admins)->min('min_point');
                $max = SummativePerformnceLevel::whereIn('created_by', $admins)->max('max_point');

                return view('summative-assessments.assessment', compact('terms', 'class', 'class_slug', 'stream_slug', 'learners', 'subject', 'stream', 'min', 'max'));
            }
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        try {
            $input = $request->except('_token');
            $learners = $input['learners'];
            $points = $input['points'];
            unset($input['learners'], $input['points']);

            foreach ($learners as $key => $learner) {
                unset($input['points'], $input['performance_level_id']);
                $point = (float)$points[$key];
                $input['learner_id'] = $learner;
                SummativeAssessment::where($input)->delete();
                $input['points'] = $point;
                $level = SummativePerformnceLevel::where('min_point', '<=', $point)
                    ->where('max_point', '>=', $point)->first();
                $input['performance_level_id'] = $level->id;

                SummativeAssessment::create($input);
            }

            return redirect()->back()->with('success', 'Summative Assessment Created');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getAssessments(Request $request)
    {
        try {
            $input = $request->all();
            $assessments = SummativeAssessment::where($input['data'])
                ->with('level')
                ->get();

            return $assessments;
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function learnersView()
    {
        try {
            $classes = SchoolClass::where('school_id', Auth::user()->school_id)->get();
            $terms = Term::where('school_id', Auth::user()->school_id)->get();

            return view('summative-assessments.reports', compact('classes', 'terms'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getLearners(Request $request)
    {
        try {
            $input = $request->all();
            $users = User::where([
                'stream_id' => $input['stream_id'],
                'role' => 'learner',
                'status' => 'active'
            ])
                ->with('summative_assessments', function ($q) use ($input) {
                    return $q->where([
                        'stream_id' => $input['stream_id'],
                        'term_id' => $input['term_id'],
                        'subject_id' => $input['subject_id']
                    ])
                        ->with('level');
                })
                ->whereHas('summative_assessments')
                ->get();

            $data = [];
            $total = 0;
            foreach ($users as $user) {
                $level = $score = '';
                if (!empty($user->summative_assessments[0]) && !empty($user->summative_assessments[0]->level)) {
                    $level = $user->summative_assessments[0]->level->title;
                    $score = $user->summative_assessments[0]->points;
                }
                $total += $score;
                $data[] = [
                    'remark' => $level,
                    'score' => $score,
                    'name' => $user->name,
                    'admission_number' => $user->admission_number,
                    'id' => $user->id
                ];
            }

            $class_average = round($total / $users->count(), 2);
            $remark = checkSummetiveCriteria($class_average);

            $data[] = [
                'remark' => $remark,
                'score' => $class_average . '%',
                'admission_number' => 'Class Average',
                'name' => '',
                'id' => null
            ];

            return Datatables::of($data)
                ->make(true);
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    public function viewResult($learner_id, $term_id, $stream_id, $exam_id)
    {
        try {
            $stream = Stream::where('id', $stream_id)
                ->with('school_class')
                ->first();
            $learner = User::where('id', $learner_id)->first();
            $assessments = SummativeAssessment::where([
                'stream_id' => $stream_id,
                'term_id' => $term_id,
                'learner_id' => $learner_id,
                'exam_id' => $exam_id
            ])
                ->with(['level', 'subject'])
                ->get();
            $term = Term::where('id', $term_id)->first();

            return view('summative-assessments.report', compact('learner', 'stream', 'assessments', 'term'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param $learner_id
     * @param $stream_id
     * @param $term_id
     * @param $send_email
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadPdf($learner_id, $stream_id, $term_id, $exam_id, $send_email = false)
    {
        try {
            $school = getSchoolSettings();
            $stream = Stream::where('id', $stream_id)
                ->with('school_class')
                ->first();

            $learner = User::find($learner_id);
            $term = Term::find($term_id);
            $admins = getSchoolAdmins($school->id);
            $levels = SummativePerformnceLevel::whereIn('created_by', $admins)->get();
            $assessments = SummativeAssessment::where([
                'stream_id' => $stream_id,
                'term_id' => $term_id,
                'learner_id' => $learner_id,
                'exam_id' => $exam_id
            ])
                ->with(['level', 'subject'])
                ->get();

            $data = [
                'school' => $school,
                'stream' => $stream,
                'term' => $term,
                'learner' => $learner,
                'assessments' => $assessments,
                'levels' => $levels,
                'admins' => $admins
            ];

            $pdf = PDF::loadView('pdfs.summative-result', $data);
            if ($send_email) {
                $content = $pdf->output();
                \Storage::put('public/reports/' . $learner->name . '/' . 'report_card_' . $term->term . '.pdf', $content);
                $details = [
                    'title' => 'Report Card',
                    'body' => 'Please download the file for view ',
                    'email' => $learner->parent_email,
                    'show_btns' => 0,
                    'link' => null,
                    'subject' => 'Report Card',
                    'file' => \Storage::disk('public')->path('reports/' . $learner->name . '/' . 'report_card_' . $term->term . '.pdf')
                ];

                dispatch(new EmailJob($details));
                \Storage::delete('public/reports/' . $learner->name . '/' . 'report_card_' . $term->term . '.pdf');
                return redirect()->back()->with('success', 'Email Sent');
            } else {
                return $pdf->stream('summative_report_card_' . $term->term . '.pdf');
            }
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    public function generateClassPdf(Request $request) {
        try {
            $input = $request->except('_token');
//            dd($input);
            $teacher = TeacherManagement::where([
                'class_id' => $input['class_id'],
                'stream_id' => $input['stream_id'],
            ])->with('teacher')->first();
            $exam = Exam::where('id', $input['exam_id'])->first();
            $subject = Subjects::find($input['subject_id']);
            $school = getSchoolSettings();
            $stream = Stream::where('id', $input['stream_id'])
                ->with('school_class', function ($q) {
                    return $q->with('class_subjects', function ($q) {
                        return $q->with('subject');
                    });
                })
                ->first();
            $users = User::where([
                'stream_id' => $input['stream_id'],
                'role' => 'learner',
                'status' => 'active'
            ])
                ->with('summative_assessments', function ($q) use ($input) {
                    return $q->where([
                        'stream_id' => $input['stream_id'],
                        'term_id' => $input['term_id'],
                        'subject_id' => $input['subject_id']
                    ])
                        ->with('level');
                })
                ->whereHas('summative_assessments')
                ->get();

            $result = [];
            $total = 0;
            foreach ($users as $user) {
                $level = $score = '';
                if (!empty($user->summative_assessments[0]) && !empty($user->summative_assessments[0]->level)) {
                    $level = $user->summative_assessments[0]->level->title;
                    $score = $user->summative_assessments[0]->points;
                }
                $total += $score;
                $result[] = [
                    'remark' => $level,
                    'score' => $score,
                    'name' => $user->name,
                    'admission_number' => $user->admission_number,
                    'id' => $user->id
                ];
            }

            $term = Term::find($input['term_id']);
            $admins = getSchoolAdmins($school->id);
            $levels = SummativePerformnceLevel::whereIn('created_by', $admins)->latest()->get();

            $data = [
                'school' => $school,
                'stream' => $stream,
                'results' => $result,
                'term' => $term,
                'levels' => $levels,
                'exam' => $exam,
                'subject' => $subject,
                'teacher' => $teacher
            ];

            $pdf = PDF::loadView('pdfs.summative-class', $data);
            return $pdf->stream('class_summative_report_' . $stream->school_class->class . '_' . $stream->title  . '_' .  $term->term . '.pdf');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    public function saveLearnerAssessment(Request $request) {
        try {
            $input = $request->all();
            $point = (float)$input['points'];
            unset($input['points']);
            SummativeAssessment::where($input)->delete();
            $level = SummativePerformnceLevel::where('min_point', '<=', $point)
                ->where('max_point', '>=', $point)->first();
            $input['performance_level_id'] = $level->id;
            $input['points'] = $point;

            SummativeAssessment::create($input);
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }
}
