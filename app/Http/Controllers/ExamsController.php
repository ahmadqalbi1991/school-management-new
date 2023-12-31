<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ExamsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $exam = null;
            $term = null;
            $terms = [];
            if ($request->has('edit') && $request->get('pass_key')) {
                $exam = Exam::where(['id' => $request->get('pass_key')])->first();
                $term = Term::where('id', $exam->term_id)->first();
                $terms = Term::where(['school_id' => Auth::user()->school_id, 'year' => $term->year])
                    ->latest()
                    ->get();
            }

            return view('exams.index', compact('exam','terms', 'term'));
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
        $data = Exam::where('school_id', Auth::user()->school_id)->with('term')->get();
        $hasManagePermission = Auth::user()->can('manage_terms');

        return Datatables::of($data)
            ->addColumn('term', function ($data) use ($hasManagePermission) {
                return $data->term->term;
            })
            ->addColumn('action', function ($data) use ($hasManagePermission) {
                $output = '';
                if ($hasManagePermission) {
                    $output = '<div class="">
                                    <a href="' . route('exams.index', ['edit' => 1, 'pass_key' => $data->id]) . '"><i class="ik ik-edit f-16 text-blue"></i></a>
                                    <a href="' . route('exams.delete', ['id' => $data->id]) . '"><i class="ik ik-trash-2 f-16 text-red"></i></a>
                                </div>';
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $input = $request->except('_token');
            $input['school_id'] = Auth::user()->school_id;
            Exam::create($input);

            return redirect()->back()->with('success', 'Exam created successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $input = $request->except('_token');
            $input['school_id'] = Auth::user()->school_id;
            if (empty($input['exam_lock'])) {
                $input['exam_lock'] = 0;
            }
            Exam::where('id', $id)->update($input);

            return redirect()->back()->with('success', 'Exam updated successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Exam::where('id', $id)->delete();

            return redirect()->back()->with('success', 'Exam delete successfully');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }
}
