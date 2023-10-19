<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Auth;

class StreamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $stream = null;
            if ($request->has('edit') && $request->get('pass_key')) {
                $stream = Stream::where(['id' => $request->get('pass_key')])->first();
            }
            $classes = SchoolClass::when(!empty($stream), function ($q) use ($stream) {
                return $q->where('school_id', $stream->school_id);
            })->get();

            $schools = getSchools();

            return view('streams.index', compact('stream', 'classes', 'schools'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function getList()
    {
        $data = Stream::with('school')
            ->when(Auth::user()->role === 'admin', function ($q) {
                return $q->where('school_id', Auth::user()->school_id);
            })
            ->with([
                'assigned_subjects',
                'learner_subjects',
                'student_assessments',
                'summative_assessments',
                'teachers_management',
                'learners'
            ])
            ->latest()
            ->get();
        $hasManagePermission = Auth::user()->can('manage_streams');

        return Datatables::of($data)
            ->addColumn('class', function ($data) {
                if (!empty($data->school_class)) {
                    return $data->school_class->class;
                }

                return "";
            })
            ->addColumn('school', function ($data) {
                return $data->school->school_name;
            })
            ->addColumn('action', function ($data) use ($hasManagePermission) {
                $output = '';
                if ($hasManagePermission) {
                    $output .= '<div class="">';
                    $output .= '<a href="' . route('streams.index', ['edit' => 1, 'pass_key' => $data->id]) . '"><i class="ik ik-edit f-16 text-blue"></i></a>';
                    if ($data->assigned_subjects->count() === 0 && $data->learner_subjects->count() === 0 && $data->student_assessments->count() === 0 && $data->summative_assessments->count() === 0 && $data->learners->count() === 0 && $data->teachers_management->count() === 0) {
                        $output .= '<a href="' . route('streams.delete', ['id' => $data->id]) . '"><i class="ik ik-trash-2 f-16 text-red"></i></a>';
                    }
                    $output .= '</div>';
                }

                return $output;
            })
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $input = $request->except('_token');;
            $input['slug'] = Str::slug($input['title']);

            Stream::create($input);
            return redirect()->back()->with('success', 'Stream Added Successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $input = $request->except('_token');
            $input['slug'] = Str::slug($input['title']);

            Stream::where('id', $id)->update($input);
            return redirect()->back()->with('success', 'Stream Updated Successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Stream::where('id', $id)->delete();
            return redirect()->back()->with('success', 'Stream Deleted Successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param $id
     * @param $move
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function getLearners($id, $move = false)
    {
        try {
            $learners = User::where([
                'stream_id' => $id,
                'role' => 'learner'
            ])->get();
            if (!$move) {
                $html = '<option value="">Select Learner(s)</option>';
                foreach ($learners as $learner) {
                    $html .= '<option value="' . $learner->id . '">' . $learner->name . '</option>';
                }
                return $html;
            } else {
                return Datatables::of($learners)
                    ->addColumn('checkbox', function ($data) {
                        return '<input type="checkbox" name="learners[]" class="learner-checkboxes" value="' . $data->id . '" id="learner_checkbox_' . $data->id . '" />';
                    })
                    ->rawColumns(['checkbox'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }
}
