<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\StudentAssessment;
use Illuminate\Http\Request;
use App\Models\Subjects;
use App\Models\SummativeAssessment;
use App\Models\User;
use Auth;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|void
     */
    public function dashboard()
    {
        try {
            if (Auth::user()->role === 'super_admin') {
                $total_schools = School::get();
                $new_schools = School::latest()->limit(10)->get();
                $users = User::all();
                $subjects = Subjects::all();
                $teachers = User::where('role', 'teacher')->with('school')->get();

                return view('dashboard.super-admin', compact('total_schools', 'teachers', 'users', 'subjects', 'new_schools'));
            }

            if (Auth::user()->role === 'admin' || Auth::user()->role === 'teacher') {
                $users = User::where('school_id', Auth::user()->school_id)->get();
                $subjects = Subjects::all();
                $teachers = User::where('role', 'teacher')->with('school')->get();
                $classes = SchoolClass::where('school_id', Auth::user()->school_id)->get();
                $total_classes = [];
                if ($classes->count()) {
                    $total_classes = $classes->pluck('id')->toArray();
                }
                $formative_assessments = StudentAssessment::whereIn('class_id', $total_classes)->get()->groupBy('subject_id');
                $summative_assessments = SummativeAssessment::whereIn('class_id', $total_classes)->get()->groupBy('subject_id');

                return view('dashboard.admin', compact('teachers', 'users', 'subjects', 'formative_assessments', 'summative_assessments'));
            }
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function profile() {
        try {
            $user = User::where('id', Auth::id())
                ->with(['school', 'activities'])
                ->with('streams', function ($q) {
                    return $q->with(['class', 'stream']);
                })
                ->with('subjects', function ($q) {
                    return $q->with('subject');
                })
                ->first();

            return view('pages.profile', compact('user'));
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    public function updateProfile(Request $request, $id) {
        try {
            $input = $request->except('_token');
            $user = User::where('id', $id)->first();
            if ($request->has('profile_image')) {
                $imageName = time().'.'.$request->profile_image->extension();
                $request->profile_image->move(public_path('images/users/profile_images'), $imageName);
                $user->profile_image = 'images/users/profile_images/' . $imageName;
            }

            $user->name = $input['name'];
            $user->phone_number = $input['phone_number'];
            $user->tsc_number = $input['tsc_number'];
            $user->save();

            return redirect()->back()->with('success', 'Profile successfully updated');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }
}
