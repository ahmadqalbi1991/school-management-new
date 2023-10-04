<?php

namespace App\Http\Controllers;

use App\Jobs\EmailJob;
use App\Models\AssignedSubject;
use App\Models\Exam;
use App\Models\LearnerSubject;
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
                    ->paginate(8);

                return view('summative-assessments.streams', compact('streams'));
            }

            if ($class_slug && $stream_slug && !$subject_slug) {
                $classObj = SchoolClass::where(['slug' => $class_slug, 'school_id' => Auth::user()->school_id])->first();
                $subjects = getSchoolSubjects(false);
                $stream = Stream::where('school_id', Auth::user()->school_id)
                    ->where([
                        'slug' => $stream_slug,
                        'class_id' => $classObj->id
                    ])
                    ->first();

                $exist = TeacherManagement::where([
                    'teacher_id' => Auth::id(),
                    'stream_id' => $stream->id,
                    'class_id' => $classObj->id
                ])->first();

                if (!$exist) {
                    return redirect()->back()->with('error', 'You don`t have access to this page');
                }

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
                $subject = Subjects::where('slug', $subject_slug)->first();
                $exist = AssignedSubject::where(['teacher_id' => Auth::id(), 'subject_id' => $subject->id])->first();
                if (!$exist) {
                    return redirect()->back()->with('error', 'You don`t have access to this page');
                }
                $assigned_learners = LearnerSubject::where([
                    'class_id' => $class->id,
                    'stream_id' => $stream->id,
                    'subject_id' => $subject->id
                ])->get();
                $learners = User::where([
                    'school_id' => Auth::user()->school_id,
                    'stream_id' => $stream->id
                ])
                    ->whereIn('id', $assigned_learners->pluck('learner_id')->toArray())
                    ->orderBy('admission_number', 'asc')
                    ->get();
                $terms = Term::where('school_id', Auth::user()->school_id)->get();
                $admins = getSchoolAdmins();
                $min = SummativePerformnceLevel::whereIn('created_by', $admins)->min('min_point');
                $max = SummativePerformnceLevel::whereIn('created_by', $admins)->max('max_point');
                $levels = SummativePerformnceLevel::whereIn('created_by', $admins)->count();

                return view('summative-assessments.assessment', compact('terms', 'levels', 'class', 'class_slug', 'stream_slug', 'learners', 'subject', 'stream', 'min', 'max'));
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
            $exam_lock = checkSummativeExamLock($input['exam_id']);
            if ($exam_lock) {
                return redirect()->back()->with('error', 'Exam is locked, you are not allowed update or create this exam');
            }
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
                dd($input);

                SummativeAssessment::create($input);
            }
            saveActivity(' saved the summative assessment');

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
            $data['lock_exam'] = checkSummativeExamLock($input['data']['exam_id']);
            $assessments = SummativeAssessment::where($input['data'])
                ->with('level')
                ->with('learner')
                ->get();
            $summative_learners = $assessments->pluck('learner');
            $learners = [];
            $option = '';
            foreach ($summative_learners as $learner) {
                $learners = collect($learners);
                $exist_learner = $learners->where('id', $learner->id)->first();
                if (empty($exist_learner)) {
                    $learners[] = [
                        'id' => $learner->id,
                        'name' => $learner->name
                    ];
                    $option .= '<option value="' . $learner->id . '">' . $learner->name . '</option>';
                }
            }


            $data['assessments'] = $assessments;
            $data['learners'] = $option;

            return $data;
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function classReports()
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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function learnersReports()
    {
        try {
            $classes = SchoolClass::where('school_id', Auth::user()->school_id)->get();

            return view('summative-assessments.learners-reports', compact('classes'));
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
            $assessments = SummativeAssessment::where([
                'stream_id' => $input['stream_id'],
                'term_id' => $input['term_id'],
                'subject_id' => $input['subject_id'],
                'exam_id' => $input['exam_id'],
            ])
                ->get();

            $learners = [];
            if ($assessments->count()) {
                $learners = array_unique($assessments->pluck('learner_id')->toArray());
            }

            $users = User::where([
                'role' => 'learner',
                'status' => 'active'
            ])
                ->whereIn('id', $learners)
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

            dd($users);

            $data = [];
            $total = 0;
            foreach ($users as $user) {
                $level = '';
                $score = 0;
                if (!empty($user->summative_assessments[0]->level)) {
                    $level = $user->summative_assessments[0]->level->title;
                    $score = $user->summative_assessments[0]->points;
                }
                $total += $score;
                $data[] = [
                    'remark' => $level,
                    'score' => $score,
                    'name' => $user->name,
                    'admission_number' => $user->admission_number,
                    'id' => $user->id,
                    'checkbox' => true
                ];
            }

            if ($assessments->count()) {
                $class_average = round($total / $assessments->count(), 2);
            } else {
                $class_average = 0;
            }
            $remark = checkSummetiveCriteria($class_average);

            $data[] = [
                'remark' => $remark,
                'score' => $class_average . '%',
                'admission_number' => 'Class Average',
                'name' => '',
                'id' => null,
                'checkbox' => false
            ];

            return Datatables::of($data)
                ->addColumn('checkbox', function ($record) {
                    if ($record['checkbox']) {
                        return '<input type="checkbox" name="learners[]" class="learner-checkboxes" value="' . $record['id'] . '" id="learner_checkbox_' . $record['id'] . '" />';
                    }
                })
                ->addColumn('action', function ($record) use ($input) {
                    if ($record['id']) {
                        $output = '<div class="">
                                        <a href="' . route('summative-reports.download-pdf', ['learner_id' => $record['id'], 'term_id' => $input['term_id'], 'stream_id' => $input['stream_id'], 'exam_id' => $input['exam_id']]) . '"><i class="fas fa-file-pdf f-16 text-pink"></i></a>
                                        <a href="' . route('summative-reports.download-pdf', ['learner_id' => $record['id'], 'term_id' => $input['term_id'], 'stream_id' => $input['stream_id'], 'exam_id' => $input['exam_id'], 'send-email' => 1]) . '"><i class="fas fa-envelope f-16 text-blue"></i></a>
                                        <a href="' . route('summative-reports.view-result', ['learner_id' => $record['id'], 'term_id' => $input['term_id'], 'stream_id' => $input['stream_id'], 'exam_id' => $input['exam_id']]) . '"><i class="fas fa-eye f-16 text-green"></i></a>
                                        </div>';

                        return $output;
                    }
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param $learner_id
     * @param $term_id
     * @param $stream_id
     * @param $exam_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function viewResult($learner_id, $term_id, $stream_id, $exam_id)
    {
        try {
            $stream = Stream::where('id', $stream_id)
                ->with('school_class')
                ->first();
            $learner = User::where('id', $learner_id)->first();
            $assessments = LearnerSubject::where(['stream_id' => $stream_id, 'learner_id' => $learner_id])
                ->with('assessment', function ($q) use ($stream_id, $exam_id, $term_id, $learner_id) {
                    return $q->where([
                        'stream_id' => $stream_id,
                        'exam_id' => $exam_id,
                        'term_id' => $term_id,
                        'learner_id' => $learner_id
                    ]);
                })
                ->with('subject')
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
            $data = self::generatePdf($learner_id, $stream_id, $term_id, $exam_id);
            $learner = $data['learner'];
            $term = $data['term'];

            return view('pdfs.summative-result')->with($data);
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateClassPdf(Request $request)
    {
        try {
            $input = $request->except('_token');
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
            $assessments = SummativeAssessment::where([
                'stream_id' => $input['stream_id'],
                'term_id' => $input['term_id'],
                'subject_id' => $input['subject_id'],
                'exam_id' => $input['exam_id'],
            ])
                ->get();

            $learners = [];
            if ($assessments->count()) {
                $learners = array_unique($assessments->pluck('learner_id')->toArray());
            }
            $users = User::where([
                'role' => 'learner',
                'status' => 'active'
            ])
                ->whereIn('id', $learners)
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
                    $score = !empty($user->summative_assessments[0]->points) ? $user->summative_assessments[0]->points : 0;
                }
                if ($score == '') {
                    $score = 0;
                }
                $total += (float)$score;
                $result[] = [
                    'remark' => $level,
                    'score' => $score,
                    'name' => $user->name,
                    'admission_number' => $user->admission_number,
                    'id' => $user->id
                ];
            }

            $term = Term::find($input['term_id']);
            $next_term = Term::where('school_id', $school->id)
                ->whereDate('start_date', '>', $term->end_date)
                ->first();
            $admins = getSchoolAdmins($school->id);
            $levels = SummativePerformnceLevel::whereIn('created_by', $admins)->latest()->get();

            $data = [
                'school' => $school,
                'stream' => $stream,
                'results' => $result,
                'term' => $term,
                'next_term' => $next_term,
                'levels' => $levels,
                'exam' => $exam,
                'subject' => $subject,
                'teacher' => $teacher
            ];

            $pdf = PDF::loadView('pdfs.summative-class', $data);
            return $pdf->stream('class_summative_report_' . $stream->school_class->class . '_' . $stream->title . '_' . $term->term . '.pdf');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function saveLearnerAssessment(Request $request)
    {
        try {
            $input = $request->all();
            $exam_lock = checkSummativeExamLock($input['exam_id']);
            if (!$exam_lock) {
                return redirect()->back()->with('error', 'Exam is locked, you are not allowed update or create this exam');
            }
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

    /**
     * @param $learner_id
     * @param $stream_id
     * @param $term_id
     * @param $exam_id
     * @return array
     */
    private static function generatePdf($learner_id, $stream_id, $term_id, $exam_id)
    {
        $school = getSchoolSettings();
        $stream = Stream::where('id', $stream_id)
            ->with('school_class')
            ->first();

        $learner = User::find($learner_id);
        $term = Term::find($term_id);
        $next_term = Term::where('school_id', $school->id)
            ->whereDate('start_date', '>', $term->end_date)
            ->first();
        $admins = getSchoolAdmins($school->id);
        $levels = SummativePerformnceLevel::whereIn('created_by', $admins)->orderBy('min_point')->get();
        $assessments = LearnerSubject::where(['stream_id' => $stream_id, 'learner_id' => $learner_id])
            ->with('assessment', function ($q) use ($stream_id, $exam_id, $term_id, $learner_id) {
                return $q->where([
                    'stream_id' => $stream_id,
                    'exam_id' => $exam_id,
                    'term_id' => $term_id,
                    'learner_id' => $learner_id
                ]);
            })
            ->with('subject')
            ->whereHas('subject')
            ->get();

        return [
            'school' => $school,
            'stream' => $stream,
            'term' => $term,
            'next_term' => $next_term,
            'learner' => $learner,
            'assessments' => $assessments,
            'levels' => $levels,
            'admins' => $admins
        ];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDownloadPdf(Request $request)
    {
        try {
            $input = $request->except('_token');

            if (!empty($input['all_students'])) {
                $assessments = SummativeAssessment::where([
                    'class_id' => $input['class_id'],
                    'stream_id' => $input['stream_id'],
                    'term_id' => $input['term_id'],
                    'exam_id' => $input['exam_id'],
                ])->get();

                $learners = [];
                if ($assessments->count()) {
                    $learners = $assessments->pluck('learner_id')->toArray();
                    $learners = array_unique($learners);
                }
            } else {
                $learners = $input['learners'];
            }

            $html = '';
            foreach ($learners as $learner) {
                $data = self::generatePdf($learner, $input['stream_id'], $input['term_id'], $input['exam_id']);
                $term = $data['term'];
                $view = view('pdfs.summative-result')->with($data);
                $html .= $view->render();
            }

            $pdf = PDF::loadHtml($html);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream('report_card_' . $term->term . '.pdf');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    public function getReportLearners(Request $request)
    {
        try {
            $input = $request->all();
            $assessments = SummativeAssessment::where([
                'class_id' => $input['class_id'],
                'stream_id' => $input['stream_id'],
                'term_id' => $input['term_id'],
            ])
                ->whereIn('exam_id', $input['exam_ids'])
                ->with('level')
                ->with('learner')
                ->get();

            $learners = [];
            $option = '';
            if ($assessments->count()) {
                $summative_learners = $assessments->pluck('learner');
                foreach ($summative_learners as $learner) {
                    $learners = collect($learners);
                    $exist_learner = $learners->where('id', $learner->id)->first();
                    if (empty($exist_learner)) {
                        $learners[] = [
                            'id' => $learner->id,
                            'name' => $learner->name
                        ];
                        $option .= '<option value="' . $learner->id . '">' . $learner->name . '</option>';
                    }
                }
            }

            return $option;
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }
}
