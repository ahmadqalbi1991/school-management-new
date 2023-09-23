<?php

namespace App\Http\Controllers;

use App\Models\AssignedSubjectsClass;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\SummativeAssessment;
use App\Models\SummativePerformnceLevel;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class ConsolidateReportController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        try {
            $classes = SchoolClass::where('school_id', Auth::user()->school_id)->get();

            return view('consolidate-reports.index', compact('classes'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateReports(Request $request)
    {
        try {
            $input = $request->except('_token');
            if (!empty($input['all_students'])) {
                $ids = SummativeAssessment::where([
                    'class_id' => $input['class_id'],
                    'stream_id' => $input['stream_id'],
                    'term_id' => $input['term_id'],
                ])->get();
                $learners_ids = [];
                if ($ids->count()) {
                    $learners_ids = array_unique($ids->pluck('learner_id')->toArray());
                }
            } else {
                $learners_ids = $input['learners_ids'];
            }
            $exams = Exam::whereIn('id', $input['exam_ids'])->get();
            $school = getSchoolSettings();
            $stream = Stream::where('id', $input['stream_id'])
                ->with('school_class')
                ->first();
            $term = Term::find($input['term_id']);
            $next_term = Term::where('school_id', $school->id)
                ->whereDate('start_date', '>', $term->end_date)
                ->first();
            $admins = getSchoolAdmins($school->id);
            $levels = SummativePerformnceLevel::whereIn('created_by', $admins)->get();
            $assigned_subjects = AssignedSubjectsClass::where([
                'school_id' => Auth::user()->school_id,
                'class_id' => $input['class_id']
            ])
                ->with('subject')
                ->get();
            $html = '';
            foreach ($learners_ids as $learner_key => $learner_id) {
                $reports = [];
                $learner = User::find($learner_id);
                foreach ($assigned_subjects as $subject_key => $subject) {
                    $reports[$subject_key]['subject'] = $subject->subject->title;
                    $reports[$subject_key]['points'] = [];
                    foreach ($exams as $exam_key => $exam) {
                        $assessment = SummativeAssessment::where([
                            'class_id' => $input['class_id'],
                            'stream_id' => $input['stream_id'],
                            'term_id' => $input['term_id'],
                            'learner_id' => $learner_id,
                            'exam_id' => $exam->id,
                            'subject_id' => $subject->subject_id
                        ])
                            ->with('subject')
                            ->first();

                        $reports[$subject_key]['points'][$exam_key] = 0;
                        if (!empty($assessment)) {
                            $reports[$subject_key]['points'][$exam_key] = $assessment->points;
                        }
                    }

                    if (array_sum($reports[$subject_key]['points']) == 0) {
                        unset($reports[$subject_key]);
                    }
                }
                $view = view('pdfs.consolidate-report', compact('exams', 'learner', 'levels', 'school', 'term', 'stream', 'reports', 'next_term'));
                $html .= $view;
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
