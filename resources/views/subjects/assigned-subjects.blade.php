@extends('layouts.main')
@section('title', 'Subjects')
@section('content')
    <!-- push external head elements to head -->
    @push('head')
        <link rel="stylesheet" href="{{ asset('plugins/DataTables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/select2/dist/css/select2.min.css') }}">
    @endpush


    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-unlock bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('Subjects')}}</h5>
                            <span>{{ empty($subject) ? __('Add subject details') : __('Edit subject details') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">{{ __('Subject')}}</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <!-- start message area-->
            @include('include.message')
            <!-- end message area-->
            <!-- only those have manage_permission permission will get access -->
            @can('manage_subjects')
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header"><h3>{{ empty($subject) ? __('Add subject') : __('Edit subject') }}</h3></div>
                        <div class="card-body">
                            <form class="forms-sample" method="POST" data-parsley-validate
                                  action="{{ route('subjects.assign-subject') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="subject_id">{{ __('Subjects')}}<span class="text-red">*</span></label>
                                            <select name="subject_id[]" id="subject_id" multiple class="form-control select2">
                                                <option value="">{{ __('Select Subjects') }}</option>
                                                @foreach($subjects as $subject)
                                                    <option value="{{ $subject->id }}">{{ $subject->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="school_id">{{ __('School')}}<span class="text-red">*</span></label>
                                            <select name="school_id" id="school_id" class="form-control select2">
                                                <option value="">{{ __('Select School') }}</option>
                                                @foreach($schools as $school)
                                                    <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="class_id">{{ __('Classes')}}<span class="text-red">*</span></label>
                                            <select name="class_id" id="class_id" class="form-control select2">
                                                <option value="">{{ __('Select Classes') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 text-right">
                                        <div class="form-group">
                                            <button type="submit"
                                                    class="btn btn-success btn-rounded">{{ __('Save')}}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
    <!-- push external js -->
    @push('script')
        <script src="{{ asset('plugins/select2/dist/js/select2.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/Cell-edit/dataTables.cellEdit.js') }}"></script>
        <!--server side permission table script-->
        <script src="{{ asset('js/subjects.js') }}"></script>
    @endpush
@endsection
