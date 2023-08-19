<?php

namespace App\Http\Controllers;

use App\Models\AssignedSubject;
use App\Models\AssignedSubjectsClass;
use App\Models\ClassSubject;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subjects;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class SubjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $subject = null;
            if ($request->has('edit') && $request->get('pass_key')) {
                $subject = Subjects::where(['id' => $request->get('pass_key')])->first();
            }
            $classes = SchoolClass::when(Auth::user()->role === 'admin', function ($q) {
                return $q->where('school_id', Auth::user()->school_id);
            })->get();

            return view('subjects.index', compact('subject', 'classes'));
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
        $data = [];
        if (Auth::user()->role === 'super_admin') {
            $data = Subjects::with('school_class')->latest()->get();
        } else if (Auth::user()->role === 'admin') {
//            $data = getSchoolSubjects();
            $data = AssignedSubjectsClass::where('school_id', Auth::user()->school_id)
                ->with(['subject', 'school_class'])
                ->get();
        }
        $hasManagePermission = Auth::user()->can('manage_subjects');

        return Datatables::of($data)
            ->addColumn('class', function ($data) {
                $class = '';
                if (!empty($data->school_class)) {
                    $class = $data->school_class->class;
                }

                return $class;
            })
            ->addColumn('title', function ($data) {
                $subject = '';
                if (Auth::user()->role === 'admin') {
                    if (!empty($data->subject)) {
                        $subject = $data->subject->title;
                    }
                }

                if (Auth::user()->role === 'super_admin') {
                    $subject = $data->title;
                }

                return $subject;
            })
            ->addColumn('action', function ($data) use ($hasManagePermission) {
                $output = '';
                if ($hasManagePermission) {
                    $output = '<div class="">
                                    <a href="' . route('subjects.index', ['edit' => 1, 'pass_key' => $data->id]) . '"><i class="ik ik-edit f-16 text-blue"></i></a>
                                    <a href="' . route('subjects.delete', ['id' => $data->id]) . '"><i class="ik ik-trash-2 f-16 text-red"></i></a>
                                </div>';
                }

                return $output;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
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
            $input = $request->except('_token');
            $input['slug'] = Str::slug($input['title']);
            Subjects::create($input);

            return redirect()->back()->with('success', 'Subject added successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
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
            Subjects::where('id', $id)->update($input);

            return redirect()->back()->with('success', 'Subject updated successfully');
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
            $subject = Subjects::where('id', $id)->with('strands')->first();
            $strands = $subject->strands;
            foreach ($strands as $strand) {
                foreach ($strand->sub_strands as $sub_strand) {
                    $sub_strand->learning_activities()->delete();
                }
                $strand->sub_strands()->delete();
            }
            $subject->strands()->delete();
            $subject->terms()->delete();
            $subject->delete();

            return redirect()->back()->with('success', 'Subject deleted successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function assignedSubjects(Request $request)
    {
        try {
            $subjects = Subjects::all();
            $schools = School::where('active', 1)->get();
            $classes = $assigned_subject_ids = [];
            if ($request->has('school_id')) {
                $assigned_subjects = AssignedSubjectsClass::where([
                    'school_id' => $request->get('school_id'),
                    'class_id' => $request->get('class_id'),
                ])->get();

                if ($assigned_subjects->count()) {
                    $assigned_subject_ids = array_unique($assigned_subjects->pluck('subject_id')->toArray());
                }

                $classes = SchoolClass::where('school_id', $request->get('school_id'))->get();
            }

            return view('subjects.assigned-subjects', compact('subjects', 'schools', 'classes', 'assigned_subject_ids'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignSubjects(Request $request)
    {
        try {
            $input = $request->except('_token');
            $subject_ids = $input['subject_id'];
            unset($input['subject_id']);

            AssignedSubjectsClass::where(['class_id' => $input['class_id'], 'school_id' => $input['school_id']])->delete();
            foreach ($subject_ids as $id) {
                $input['subject_id'] = $id;
                AssignedSubjectsClass::create($input);
            }

            return back()->with('success', 'Subjects added to class');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    public function getAssignedList(Request $request)
    {
        try {
            $data = SchoolClass::when(Auth::user()->role !== 'super_admin', function ($q) {
                return $q->where('school_id', Auth::user()->school_id);
            })
                ->with(['school', 'assigned_subjects'])->get();

            return Datatables::of($data)
                ->addColumn('school', function ($data) {
                    return $data->school->school_name;
                })
                ->addColumn('subjects', function ($data) {
                    $output = '';
                    $subjects = $data->assigned_subjects;
                    foreach ($subjects as $subject) {
                        $output .= '<span class="badge badge-dark m-1">' . $subject->subject->title . '</span>';
                    }

                    return $output;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="">
                                <a href="?class_id=' . $data->id . '&school_id=' . $data->school->id . '"><i class="ik ik-edit f-16 text-blue"></i></a>
                            </div>';
                })
                ->rawColumns(['subjects', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }
}
