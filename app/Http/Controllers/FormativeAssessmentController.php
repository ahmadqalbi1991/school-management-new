<?php

namespace App\Http\Controllers;

use App\Jobs\EmailJob;
use App\Models\AssignedSubject;
use App\Models\LearnerSubject;
use App\Models\PerformanceLevel;
use App\Models\SchoolClass;
use App\Models\Strand;
use App\Models\Stream;
use App\Models\StudentAssessment;
use App\Models\Subjects;
use App\Models\TeacherManagement;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth, PDF, Response;
use Yajra\DataTables\DataTables;

class FormativeAssessmentController extends Controller
{
    /**
     * @param $class_slug
     * @param $stream_slug
     * @param $subject_slug
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|void
     */
    public function index(Request $request, $class_slug = null, $stream_slug = null, $subject_slug = null)
    {
        try {
            if (!$stream_slug && !$class_slug) {
                $assigned_streams = TeacherManagement::where('teacher_id', Auth::id())->get();
                $assigned_ids = $assigned_streams->pluck('stream_id')->toArray();
                $streams = Stream::where('school_id', Auth::user()->school_id)
                    ->whereIn('id', $assigned_ids)
                    ->with('school_class')
                    ->paginate(10);

                return view('formative-assessments.streams', compact('streams'));
            }

            if ($class_slug && $stream_slug && !$subject_slug) {
                $classObj = SchoolClass::where(['slug' => $class_slug, 'school_id' => Auth::user()->school_id])->first();
                $subjects = getSchoolSubjects(false, $classObj->id);
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

                return view('formative-assessments.subjects', compact('subjects', 'class_slug', 'stream_slug'));
            }

            if ($class_slug && $stream_slug && $subject_slug) {
                $subject = Subjects::where('slug', $subject_slug)
                    ->with('terms', function ($q) {
                        return $q->with('term');
                    })
                    ->with('strands')
                    ->first();
                $exist = AssignedSubject::where(['teacher_id' => Auth::id(), 'subject_id' => $subject->id])->first();
                if (!$exist) {
                    return redirect()->back()->with('error', 'You don`t have access to this page');
                }
                $class = SchoolClass::where(['slug' => $class_slug, 'school_id' => Auth::user()->school_id])->first();
                $stream = Stream::where([
                    'class_id' => $class->id
                ])->first();
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
                    ->get();
                $levels = PerformanceLevel::get();
                $terms = Term::where('school_id', Auth::user()->school_id)->get();
                $strands = $subject->strands;

                return view('formative-assessments.assessments', compact('terms', 'strands', 'levels', 'learners', 'class', 'stream', 'subject'));
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
            $term_lock = checkFormativeTermLock($input['term_id']);
            if ($term_lock) {
                return redirect()->back()->with('error', 'You are not allowed to update or create term result, because term is locked');
            }
            $assessments = $input['assessments'];
            unset($input['assessments']);
            StudentAssessment::where($input)->delete();
            $subject_assessment = [];
            $i = 0;
            foreach ($assessments as $key => $assessment) {
                $subject_assessment[$i] = $input;
                $subject_assessment[$i]['learner_id'] = $key;
                $subject_assessment[$i]['performance_level_id'] = $assessment;
                $subject_assessment[$i]['created_at'] = Carbon::now();
                $subject_assessment[$i]['updated_at'] = Carbon::now();
                $i++;
            }

            StudentAssessment::insert($subject_assessment);
            saveActivity(' saved the formative assessment');

            return redirect()->back()->with('success', 'Assessment Created');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function getAssessments(Request $request)
    {
        try {
            $input = $request->all();
            $assessments = StudentAssessment::where($input['data'])->get();
            $return_object = [];
            $term_lock = checkFormativeTermLock($input['data']['term_id']);
            $return_object['lock_term'] = $term_lock;
            foreach ($assessments as $assessment) {
                $return_object[$assessment->learner_id] = $assessment->performance_level_id;
            }

            return $return_object;
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function reports()
    {
        try {
            $classes = SchoolClass::where(['status' => 1, 'school_id' => Auth::user()->school_id])->get();
            $terms = Term::where(['school_id' => Auth::user()->school_id])->get();

            return view('formative-assessments.reports', compact('classes', 'terms'));
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
            $assessments_data = StudentAssessment::where([
                'stream_id' => $request->stream_id,
                'term_id' => $request->term_id,
            ])->get();
            $learners = [];
            if ($assessments_data->count()) {
                $learners = $assessments_data->pluck('learner_id')->toArray();
                $learners = array_unique($learners);
            }

            $data = User::where([
                'role' => 'learner',
                'status' => 'active',
            ])
                ->whereIn('id', $learners)
                ->with('assessments', function ($q) use ($request) {
                    return $q->where('term_id', $request->term_id);
                })
                ->whereHas('assessments')
                ->get();
            $hasManagePermission = Auth::user()->can('manage_formative_assessments');

            return Datatables::of($data)
                ->addColumn('checkbox', function ($data) {
                    return '<input type="checkbox" name="learners[]" class="learner-checkboxes" value="' . $data->id . '" id="learner_checkbox_' . $data->id . '" />';
                })
                ->addColumn('action', function ($data) use ($hasManagePermission, $request) {
                    $output = '';
                    if ($hasManagePermission) {
                        $output = '<div class="">
                                    <a target="_blank" href="' . route('reports.download-pdf', ['learner_id' => $data->id, 'stream_id' => $request->stream_id, 'term_id' => $request->term_id]) . '"><i class="fas fa-file-pdf f-16 text-pink"></i></a>
                                    <a href="' . route('reports.download-pdf', ['learner_id' => $data->id, 'stream_id' => $request->stream_id, 'term_id' => $request->term_id, 'send_email' => true]) . '"><i class="fas fa-envelope f-16 text-blue"></i></a>
                                    <a href="' . route('reports.view-subjects', [
                                'learner_id' => $data->id, 'stream_id' => $request->stream_id, 'term_id' => $request->term_id]) . '">
                                    <i class="ik ik-eye f-16 text-green"></i>
                                    </a>
                                </div>';
                    }

                    return $output;
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
     * @param $stream_id
     * @param $term_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function viewSubjects($learner_id, $stream_id, $term_id)
    {
        try {
            $stream = Stream::where('id', $stream_id)
                ->with('school_class', function ($q) {
                    return $q->with('class_subjects', function ($q) {
                        return $q->with('subject');
                    });
                })
                ->first();

            $learner_subjects = LearnerSubject::where([
                'class_id' => $stream->school_class->id,
                'stream_id' => $stream->id,
                'learner_id' => $learner_id
            ])
                ->with('subject')
                ->get();

            $result = [];
            foreach ($learner_subjects as $subject) {
                $strands = Strand::where('subject_id', $subject->subject->id)
                    ->with('sub_strands', function ($q) {
                        return $q->with('learning_activities');
                    })
                    ->get();
                $total_learning_activities = 0;
                $learning_activities_ids = [];
                if ($strands->count()) {
                    foreach ($strands as $strand) {
                        foreach ($strand->sub_strands->pluck('learning_activities') as $learning_activity) {
                            foreach ($learning_activity as $activity) {
                                $learning_activities_ids[] = $activity->id;
                            }
                            $learning_activities_ids = array_unique($learning_activities_ids);
                            $total_learning_activities += count($learning_activity);
                        }
                    }
                }

                $attempted_activities = StudentAssessment::where([
                    'learner_id' => $learner_id,
                    'subject_id' => $subject->subject->id,
                    'stream_id' => $stream_id,
                    'term_id' => $term_id,
                ])
                    ->with('level')
                    ->whereIn('learning_activity_id', $learning_activities_ids)
                    ->get();

                if ($attempted_activities->count()) {
                    $attempted_points = $attempted_activities->pluck('level')->pluck('points')->sum() / ($total_learning_activities ? $attempted_activities->pluck('level')->pluck('points')->count() : 1);
                    $result[] = [
                        'id' => $subject->subject->id,
                        'name' => $subject->subject->title,
                        'total_learning_activity' => $total_learning_activities,
                        'attempted_activities' => $attempted_activities->pluck('level')->pluck('points')->count(),
                        'attempted_points' => round($attempted_points, 2),
                    ];
                }
            }

            $learner = User::find($learner_id);
            $term = Term::find($term_id);

            return view('formative-assessments.report', compact('learner', 'result', 'stream', 'term'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param $subject_id
     * @param $learner_id
     * @param $term_id
     * @param $stream_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function viewResult($subject_id, $learner_id, $term_id, $stream_id)
    {
        try {
            $learner = User::find($learner_id);
            $subject = Subjects::where('id', $subject_id)
                ->with('strands', function ($q) {
                    return $q->with('sub_strands', function ($q) {
                        return $q->with('learning_activities');
                    });
                })
                ->first();

            $activities_defination = [];
            $strands = $subject->strands;
            $levels = PerformanceLevel::get();
            foreach ($strands as $strand_key => $strand) {
                $activities_defination[$strand_key]['title'] = $strand->title;
                $activities_defination[$strand_key]['id'] = $strand->id;
                $activities_defination[$strand_key]['sub_strands'] = [];
                $sub_strands = $strand->sub_strands;
                foreach ($sub_strands as $sub_strand_key => $sub_strand) {
                    $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['title'] = $sub_strand->title;
                    $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['id'] = $sub_strand->id;
                    $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['activities'] = [];
                    $activities = $sub_strand->learning_activities;
                    foreach ($activities as $activity_key => $activity) {
                        $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['activities'][$activity_key]['title'] = $activity->title;
                        $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['activities'][$activity_key]['id'] = $activity->id;
                        foreach ($levels as $level_key => $level) {
                            $assessment_object = StudentAssessment::where([
                                'stream_id' => $stream_id,
                                'performance_level_id' => $level->id,
                                'subject_id' => $subject_id,
                                'learner_id' => $learner_id,
                                'strand_id' => $strand->id,
                                'sub_strand_id' => $sub_strand->id,
                                'learning_activity_id' => $activity->id,
                                'term_id' => $term_id
                            ])->first();
                            $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['activities'][$activity_key]['levels'][$level_key] = null;
                            if ($assessment_object) {
                                $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['activities'][$activity_key]['levels'][$level_key]['level'] = $level->title;
                                $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['activities'][$activity_key]['levels'][$level_key]['id'] = $level->id;
                                $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['activities'][$activity_key]['levels'][$level_key]['points'] = $assessment_object->level->points;
                            }
                        }
                    }
                }
            }

            $term = Term::find($term_id);
            $stream = Stream::find($stream_id);

            return view('formative-assessments.assessment-result', compact('learner', 'subject', 'term', 'stream', 'levels', 'activities_defination'));
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
    public function downloadPdf($learner_id, $stream_id, $term_id, $send_email = false)
    {
        try {
            $data = self::generatePdf($learner_id, $stream_id, $term_id);
            $learner = $data['learner'];
            $term = $data['term'];

            $pdf = PDF::loadView('pdfs.result', $data);
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
                return $pdf->stream('report_card_' . $term->term . '.pdf');
            }

        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param $learner_id
     * @param $stream_id
     * @param $term_id
     * @return array
     */
    private static function generatePdf($learner_id, $stream_id, $term_id) {
        $school = getSchoolSettings();
        $stream = Stream::where('id', $stream_id)
            ->with('school_class', function ($q) {
                return $q->with('class_subjects', function ($q) {
                    return $q->with('subject');
                });
            })
            ->first();

        $learner_subjects = LearnerSubject::where([
            'class_id' => $stream->school_class->id,
            'stream_id' => $stream->id,
            'learner_id' => $learner_id
        ])
            ->with('subject')
            ->get();

        $result = [];
        foreach ($learner_subjects as $subject) {
            $strands = Strand::where('subject_id', $subject->subject_id)
                ->with('sub_strands', function ($q) {
                    return $q->with('learning_activities');
                })
                ->get();
            $total_learning_activities = 0;
            $learning_activities_ids = [];
            if ($strands->count()) {
                foreach ($strands as $strand) {
                    foreach ($strand->sub_strands->pluck('learning_activities') as $learning_activity) {
                        foreach ($learning_activity as $activity) {
                            $learning_activities_ids[] = $activity->id;
                        }
                        $learning_activities_ids = array_unique($learning_activities_ids);
                        $total_learning_activities += count($learning_activity);
                    }
                }
            }

            $attempted_activities = StudentAssessment::where([
                'learner_id' => $learner_id,
                'subject_id' => $subject->subject_id,
                'stream_id' => $stream_id,
                'term_id' => $term_id,
            ])
                ->with('level')
                ->whereIn('learning_activity_id', $learning_activities_ids)
                ->get();

            if ($attempted_activities->count()) {
                $total_attempted_activities = $attempted_activities->count();
                $total_attempted_activities = $total_attempted_activities > 0 ? $total_attempted_activities : 1;
                $attempted_points = $attempted_activities->pluck('level')->pluck('points')->sum() / $total_attempted_activities;
                $result[] = [
                    'id' => $subject->subject->id,
                    'name' => $subject->subject->title,
                    'total_learning_activity' => $total_learning_activities,
                    'attempted_activities' => $attempted_activities->pluck('level')->pluck('points')->count(),
                    'attempted_points' => round($attempted_points, 2),
                ];
            }
        }

        $learner = User::find($learner_id);
        $term = Term::find($term_id);
        $admins = getSchoolAdmins($school->id);
        $levels = PerformanceLevel::latest()->get();
        $data = [
            'school' => $school,
            'stream' => $stream,
            'term' => $term,
            'learner' => $learner,
            'results' => $result,
            'levels' => $levels,
            'admins' => $admins
        ];

        return $data;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDownloadPdf(Request $request) {
        try {
            $input = $request->except('_token');
            $learners = $input['learners'];
            $html = '';
            foreach ($learners as $learner) {
                $data = self::generatePdf($learner, $input['stream_id'], $input['term_id']);
                $term = $data['term'];
                $view = view('pdfs.result')->with($data);
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
}
