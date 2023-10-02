<?php

namespace App\Http\Controllers;

use App\Exports\ClassExport;
use App\Imports\LearnerImport;
use App\Jobs\EmailJob;
use App\Models\AssignedSubjectsClass;
use App\Models\ClassSubject;
use App\Models\LearnerSubject;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth, DataTables, Excel, PDF;

class LearnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $learner = null;
            if ($request->has('edit') && $request->get('pass_key')) {
                $learner = User::where(['id' => $request->get('pass_key'), 'role' => 'learner'])->first();
            }
            $schools = getSchools();
            $classes = SchoolClass::where('school_id', Auth::user()->school_id)->get();
            $streams = Stream::with(['school', 'school_class'])
                ->when(Auth::user()->role === 'admin', function ($q) {
                    return $q->where('school_id', Auth::user()->school_id);
                })
                ->get();

            return view('learners.index', compact('learner', 'schools', 'streams', 'classes'));
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
        $data = User::where(['role' => 'learner'])
            ->with('school')
            ->with('stream', function ($q) {
                return $q->with('school_class');
            })
            ->when(Auth::user()->role === 'admin', function ($q) {
                return $q->where('school_id', Auth::user()->school_id);
            })
            ->get();
        $hasManagePermission = Auth::user()->can('manage_learners');

        return Datatables::of($data)
            ->addColumn('name', function ($data) {
                return ucfirst(str_replace('_', ' ', $data->name));
            })
            ->addColumn('school', function ($data) {
                return $data->school->school_name;
            })
            ->addColumn('stream', function ($data) {
                return $data->stream->title;
            })
            ->addColumn('grade', function ($data) {
                return $data->stream->school_class->class;
            })
            ->addColumn('status', function ($data) {
                $status = '';
                if ($data->status === 'active') {
                    $status = 'success';
                }

                if ($data->status === 'disable') {
                    $status = 'danger';
                }

                if ($data->status === 'blocked') {
                    $status = 'warning';
                }
                return '<span class="badge badge-' . $status . ' m-1">' . ucfirst($data->status) . '</span>';
            })
            ->addColumn('action', function ($data) use ($hasManagePermission) {
                $output = '';
                if ($hasManagePermission) {
                    $output = '<div class="">
                                    <a href="' . route('learners.index', ['edit' => 1, 'pass_key' => $data->id]) . '"><i class="ik ik-edit f-16 text-blue"></i></a>
                                    <a href="' . route('learners-subjects.index', ['edit' => 1, 'pass_key' => $data->id]) . '"><i class="ik ik-book-open f-16 text-green"></i></a>
                                    <a href="' . route('learners.delete', ['id' => $data->id]) . '"><i class="ik ik-trash-2 f-16 text-red"></i></a>
                                </div>';
                }

                return $output;
            })
            ->rawColumns(['status', 'action'])
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
            $password = '123456789';
            $input = $request->except('_token');
            $input['password'] = bcrypt($password);
            $input['role'] = 'learner';
            $user = User::create($input);

            if ($user && !empty($input['parent_email'])) {
                $details = [
                    'title' => 'Registration Successful',
                    'body' => 'Congratulations! Your account has been created on ' . env('APP_NAME') . '. Please use the following details to login. <br/>Email: <strong>' . $input['email'] . '</strong> <br/>Password: <strong>' . $password . '</strong>',
                    'email' => $input['parent_email'],
                    'show_btns' => 1,
                    'link' => route('login'),
                    'subject' => 'Registration Successful'
                ];

                dispatch(new EmailJob($details));
            }

            return redirect()->route('learners.index')->with('success', 'Learner added successfully');
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

    public function management()
    {
        try {
            $classes = SchoolClass::where('school_id', Auth::user()->school_id)->get();

            return view('learners.management', compact('classes'));
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
            User::where('id', $id)->update($input);

            return redirect()->route('learners.index')->with('success', 'Learner updated successfully');
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
            User::where('id', $id)->delete();

            return redirect()->route('learners.index')->with('success', 'Learner deleted successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function addLearnerSubjects(Request $request)
    {
        try {
            $classes = SchoolClass::when(Auth::user()->role === 'admin', function ($q) {
                return $q->where('school_id', Auth::user()->school_id);
            })->get();
            $learner = $class_id = $learner_data = $streams = $subjects = $subjects_ids = null;
            if ($request->has('pass_key') && $request->has('edit')) {
                $learner = User::where('id', $request->get('pass_key'))
                    ->with('stream', function ($q) {
                        return $q->with('school_class');
                    })->first();

                $class_id = $learner->stream->school_class->id;
                $streams = Stream::where('class_id', $class_id)->get();
                $subjects = AssignedSubjectsClass::with('subject')
                    ->where('class_id', $class_id)->get();

                $learner_data = LearnerSubject::where(['class_id' => $class_id, 'stream_id' => $learner->stream_id, 'learner_id' => $request->get('pass_key')])
                    ->with(['subject'])->get();

                $subjects_ids = $learner_data->pluck('subject_id')->toArray();
            }

            return view('learners.add-subjects', compact('classes', 'learner', 'learner_data', 'class_id', 'streams', 'subjects', 'subjects_ids'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveLearnerSubjects(Request $request)
    {
        try {
            $input = $request->except('_token');
            $learner_ids = [];
            if (!empty($input['learner_ids'])) {
                $learner_ids = $input['learner_ids'];
                unset($input['learner_ids']);
            }

            $all_students = false;
            if (!empty($input['all_students'])) {
                $all_students = true;
                unset($input['all_students']);
            }
            $subject_ids = $input['subject_ids'];
            unset($input['subject_ids']);

            if (count($learner_ids)) {
                foreach ($learner_ids as $learner_id) {
                    LearnerSubject::where(['class_id' => $input['class_id'], 'stream_id' => $input['stream_id'], 'learner_id' => $learner_id])->delete();
                    foreach ($subject_ids as $id) {
                        $input['subject_id'] = $id;
                        $input['learner_id'] = $learner_id;
                        LearnerSubject::create($input);
                    }
                }
            }

            if ($all_students) {
                $learners = User::where([
                    'role' => 'learner',
                    'stream_id' => $input['stream_id']
                ])->get();
                $learner_ids = $learners->pluck('id')->toArray();
                foreach ($learner_ids as $learner_id) {
                    LearnerSubject::where(['class_id' => $input['class_id'], 'stream_id' => $input['stream_id'], 'learner_id' => $learner_id])->delete();
                    foreach ($subject_ids as $id) {
                        $input['subject_id'] = $id;
                        $input['learner_id'] = $learner_id;
                        LearnerSubject::create($input);
                    }
                }
            }

            return back()->with('success', 'Subjects assigned to learner');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLearnerSubjects(Request $request)
    {
        try {
            $input = $request->except('_token');
            $learner_ids = [];
            if (!empty($input['learner_ids'])) {
                $learner_ids = $input['learner_ids'];
                unset($input['learner_ids']);
            }

            $all_students = false;
            if (!empty($input['all_students'])) {
                $all_students = true;
                unset($input['all_students']);
            }
            $subject_ids = $input['subject_ids'];
            unset($input['subject_ids']);

            if (count($learner_ids)) {
                foreach ($learner_ids as $learner_id) {
                    LearnerSubject::where(['class_id' => $input['class_id'], 'stream_id' => $input['stream_id'], 'learner_id' => $learner_id])->delete();
                    foreach ($subject_ids as $id) {
                        $input['subject_id'] = $id;
                        $input['learner_id'] = $learner_id;
                        LearnerSubject::create($input);
                    }
                }
            }

            if ($all_students) {
                $learners = User::where([
                    'role' => 'learner',
                    'stream_id' => $input['stream_id']
                ])->get();
                $learner_ids = $learners->pluck('id')->toArray();
                foreach ($learner_ids as $learner_id) {
                    LearnerSubject::where(['class_id' => $input['class_id'], 'stream_id' => $input['stream_id'], 'learner_id' => $learner_id])->delete();
                    foreach ($subject_ids as $id) {
                        $input['subject_id'] = $id;
                        $input['learner_id'] = $learner_id;
                        LearnerSubject::create($input);
                    }
                }
            }

            return back()->with('success', 'Subjects assigned to learner');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        try {
            $input = $request->except('_token');
            Excel::import(new LearnerImport($input['stream_id']), $input['file']);

            return back()->with('success', 'Learners imported successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moveLearners(Request $request)
    {
        try {
            $input = $request->except('_token');
            foreach ($input['learners'] as $id) {
                User::where('id', $id)->update(['stream_id' => $input['stream_id']]);
            }

            return redirect()->back()->with('success', 'Learners moved to next class');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function classList()
    {
        try {
            $classes = SchoolClass::when(Auth::user()->role !== 'super_admin', function ($q) {
                return $q->where('school_id', Auth::user()->school_id);
            })
                ->with('school')
                ->get();

            return view('learners.class-list', compact('classes'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getClassList(Request $request)
    {
        try {
            $streams = [];
            if (!empty($request->class_id)) {
                $class = SchoolClass::where('id', $request->class_id)->first();
                if (empty($request->stream_id)) {
                    $streams = Stream::where('class_id', $class->id)->get();
                    if (!empty($streams)) {
                        $streams = $streams->pluck('id')->toArray();
                    }
                } else {
                    $streams = explode(',', $request->stream_id);
                }
            }

            $schools = School::when(Auth::user()->role !== 'super_admin', function ($q) {
                return $q->where('id', Auth::user()->school_id);
            })
            ->with('learners', function ($q) use ($streams) {
                return $q->when(!empty($streams), function ($q) use ($streams) {
                    return $q->where('stream_id', $streams);
                });
            })->get();

            $data = [];
            $i = 0;
            foreach ($schools as $school) {
                foreach ($school->learners as $learner) {
                    $data[$i]['admission_number'] = $learner->admission_number;
                    $data[$i]['learner'] = $learner->name;
                    $data[$i]['school'] = $school->school_name;
                    $data[$i]['grade'] = !empty($learner->stream) ? (!empty($learner->stream->school_class) ? $learner->stream->school_class->class : '') : '';
                    $data[$i]['stream'] = !empty($learner->stream) ? $learner->stream->title : '';
                    $i++;
                }
            }

            $data = collect($data);
            $data = $data->sortBy('school')->values();
            $data = $data->sortBy('admission_number')->values();

            $table = Datatables::of($data);
            if (Auth::user()->role === 'super_admin') {
                $table->addColumn('school', function ($data) {
                    return $data['school'];
                });
            }
            return $table->make(true);
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function classListPdf (Request $request) {
        try {
            $streams = [];
            if (!empty($request->class_id)) {
                $class = SchoolClass::where('id', $request->class_id)->first();
                if (empty($request->stream_id)) {
                    $streams = Stream::where('class_id', $class->id)->get();
                    if (!empty($streams)) {
                        $streams = $streams->pluck('id')->toArray();
                    }
                } else {
                    $streams = explode(',', $request->stream_id);
                }
            }

            if ($request->has('pdf')) {
                $classes = SchoolClass::with('school')
                    ->when(!empty($request->class_id), function ($q) use ($request) {
                        return $q->where('id', $request->class_id);
                    })
                    ->get();
                $html = '';
                foreach ($classes as $class) {
                    $learners = $class->school->learners;
                    $learners = $learners->sortBy('admission_number')->values();
                    if (!empty($streams)) {
                        $learners = $learners->whereIn('stream_id', $streams);
                    }
                    $view = view('pdfs.class-list')->with(['school' => $class->school, 'class' => $class, 'learners' => $learners]);
                    $html .= $view->render();
                }

                $pdf = PDF::loadHtml($html);
                $pdf->setPaper('a4', 'portrait');
                return $pdf->stream('class_lists.pdf');
            }

            if ($request->has('excel')) {
                return Excel::download(new ClassExport($request, $streams), 'class-list.xlsx');
            }
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }
}
