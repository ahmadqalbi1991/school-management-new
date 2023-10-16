@extends('layouts.main')
@section('title', 'Learners')
@section('content')
    <!-- push external head elements to head -->
    @push('head')
        <link rel="stylesheet" href="{{ asset('plugins/DataTables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datedropper/datedropper.min.css') }}">
    @endpush


    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <div class="d-inline">
                            <h5>{{ __('Learners')}}</h5>
                            <span>{{ __('Add learners details')}}</span>
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
                                <a href="#">{{ __('Learners')}}</a>
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
                @if(in_array(Auth::user()->role, ['admin', 'teacher']))
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row w-100">
                                    <div class="col-md-6 col-sm-12">
                                        <h3>{{ __('Subjects List')}}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <form class="forms-sample" method="POST" data-parsley-validate
                                      action="{{ route('generate-subjects-list') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="">{{ __('Grade') }}</label>
                                            <select name="class_id" id="class_id" class="form-control select2">
                                                <option value="">{{ __('Select Grade') }}</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->class }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="">{{ __('Stream') }}</label>
                                            <select name="stream_id" id="stream-id" class="form-control select2">
                                                <option value="">{{ __('Select Stream') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="">{{ __('Subject') }}</label>
                                            <select name="subject_id" id="subject-id" class="form-control select2">
                                                <option value="">{{ __('Select Subject') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 text-right">
                                        <button type="submit" disabled id="pdf-btn" class="btn btn-success btn-rounded">{{ __('Print Subjects List') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
        </div>
    </div>
    <!-- push external js -->
    @push('script')
        <script src="{{ asset('plugins/select2/dist/js/select2.min.js') }}"></script>
        <!--server side permission table script-->
        <script src="{{ asset('js/subjects.js') }}"></script>
    @endpush
@endsection
