<?php

namespace App\Http\Controllers;

use App\Models\AssignedSubjectsClass;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\SummativeAssessment;
use App\Models\Term;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Auth, PDF, Response;

class SummativeSheetController extends Controller
{
    /**
     * @return Application|Factory|View|RedirectResponse
     */
    public function index() {
        try {
            $classes = SchoolClass::where('school_id', Auth::user()->school_id)->get();

            return view('summative-assessments.summative-board-sheet', compact('classes'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    public function generateReports(Request $request) {
        try {
            $input = $request->except('_token');
            if (!empty($input['all_streams'])) {
                $streams = Stream::where('class_id', $input['class_id'])->get();
                $streams_ids = [];
                if ($streams->count()) {
                    $streams_ids = $streams->pluck('id')->toArray();
                }
            } else {
                $streams_ids = $input['stream_ids'];
                unset($input['stream_ids']);
            }

            $assessments = SummativeAssessment::where([
                'class_id' => $input['class_id'],
                'term_id' => $input['term_id']
            ])
                ->whereIn('exam_id', $input['exam_ids'])
                ->whereIn('stream_id', $streams_ids)
                ->with(['subject', 'stream', 'learner'])
                ->get();

            $learners = $assessments->groupBy('learner_id');
            $assigned_subjects = AssignedSubjectsClass::where([
                'school_id' => Auth::user()->school_id,
                'class_id' => $input['class_id']
            ])->get();
            $subject_ids = $assigned_subjects->pluck('subject_id')->toArray();

            $results = [];
            $i = 0;
            foreach ($learners as $learner_key => $learner) {
                $learner_data = User::select('admission_number', 'name')->where('id', $learner_key)->first();
                $streams = $learner->groupBy('stream_id');
                foreach ($streams as $stream_key => $stream) {
                    $stream_data = Stream::select('title')->where('id', $stream_key)->first();
                    $results[$i]['learner'] = $learner_data;
                    $results[$i]['stream'] = $stream_data;
                    $subjects = [];
                    $assessment_subjects = $stream->whereIn('subject_id', $subject_ids)->groupBy('subject_id');
                    $total_learner_points = 0;
                    foreach ($assessment_subjects as $subject_key => $subject) {
                        $points = $subject->pluck('points')->toArray();
                        $total_points = array_sum($points);
                        $average = $total_points / count($points);
                        $subjects[$subject_key]['points'] = round($average, 2);
                        $total_learner_points += $subjects[$subject_key]['points'];
                    }
                    $results[$i]['subjects'] = $subjects;
                    $results[$i]['total_learner_points'] = $total_learner_points;
                    $average = $total_learner_points / $assessment_subjects->count();
                    $results[$i]['learner_average'] = round($average, 2);
                    $i++;
                }
            }
            $results = collect($results);
            $results = $results->sortByDesc('learner_average');
            $all_points = $results->pluck('subjects');
            $learning_activity_total = [];

            foreach ($all_points as $points) {
                foreach ($points as $key => $point) {
                    if (!empty($learning_activity_total[$key])) {
                        $learning_activity_total[$key]['points'] += $point['points'];
                    } else {
                        $learning_activity_total[$key]['points'] = $point['points'];
                    }
                }
            }

            foreach ($learning_activity_total as $key => $item) {
                $available_points = $all_points->pluck($key)->whereNotNull('points')->pluck('points');
                $points = array_sum($available_points->toArray());
                $average = $points / count($available_points);
                $learning_activity_total[$key]['average'] = round($average, 2);
            }

            $data['school'] = getSchoolSettings();
            $term = Term::find($input['term_id']);
            $data['term'] = $term;
            $exams = Exam::whereIn('id', $input['exam_ids'])->get();
            $exams = implode(', ', $exams->pluck('title')->toArray());
            $data['exams'] = $exams;
            $data['subjects'] = $assigned_subjects;
            $data['results'] = $results;
            $data['totals'] = $learning_activity_total;

            $pdf = PDF::loadView('pdfs.summative-board-sheet', $data);
            $pdf->setPaper('a4', 'landscape');
            return view('pdfs.summative-board-sheet')->with($data);
//            dd($pdf, $term);
            return $pdf->stream('summative_board_sheet' . $term->term . '.pdf');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }
}
