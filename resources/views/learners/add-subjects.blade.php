@extends('layouts.main')
@section('title', 'Learners Learning Areas')
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
                            <span>{{ __('Add learners learning area')}}</span>
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
                                <a href="#">{{ __('Learners Learning Areas')}}</a>
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
            @can('manage_students_subjects')
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header"><h3>{{ __('Add Learner Learning Areas')}}</h3></div>
                        <div class="card-body">
                            <form class="forms-sample" method="POST" data-parsley-validate
                                  action="{{ empty($learner) ? route('learners-subjects.save') : route('learners-subjects.update') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="class_id">{{ __('Class')}}<span
                                                    class="text-red">*</span></label>
                                            <select name="class_id" id="class_id" class="select2 form-control">
                                                <option value="">{{ __('Select Grade') }}</option>
                                                @foreach($classes as $class)
                                                    <option
                                                        @if(!empty($learner) && $class_id === $class->id) selected
                                                        @endif value="{{ $class->id }}">{{ $class->class }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="stream-id">{{ __('Stream')}}<span
                                                    class="text-red">*</span></label>
                                            <select name="stream_id" id="stream-id"
                                                    @if(empty($learner))
                                                        disabled
                                                    @endif
                                                    class="select2 form-control">
                                                <option value="">{{ __('Select Stream') }}</option>
                                                @if(!empty($learner))
                                                    @foreach($streams as $stream)
                                                        <option
                                                            @if($learner->stream_id === $stream->id)
                                                                selected
                                                            @endif
                                                            value="{{ $stream->id }}">{{ $stream->title }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="subject-id">{{ __('Learning Areas')}}<span
                                                    class="text-red">*</span></label>
                                            <select name="subject_ids[]" id="subject-id" required
                                                    @if(empty($learner))
                                                        disabled
                                                    @endif
                                                    class="select2 form-control" multiple>
                                                @if(!empty($learner))
                                                    @foreach($subjects as $subject)
                                                        <option
                                                            @if(in_array($subject->subject_id, $subjects_ids))
                                                                selected
                                                            @endif
                                                            value="{{ $subject->subject_id }}">{{ $subject->subject->title }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label for="learner-id">{{ __('Learners')}}<span
                                                    class="text-red">*</span></label>
                                            <select name="learner_ids[]" id="learner-id"
                                                    @if(empty($learner))
                                                        disabled
                                                    @endif
                                                    class="select2 form-control" multiple>
                                                @if(!empty($learner))
                                                    <option
                                                        selected
                                                        value="{{ $learner->id }}">{{ $learner->name }}</option>
                                                @endif
                                            </select>
                                            <div class="form-check mx-2">
                                                <label class="custom-control custom-checkbox">
                                                    <input type="checkbox" value="1" name="all_students"
                                                           class="custom-control-input" id="all_learners" disabled>
                                                    <span
                                                        class="custom-control-label">&nbsp; {{ __('Assign selected learning areas to all learners') }}</span>
                                                </label>
                                            </div>
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
        <div class="row">
            <div class="col-md-12">
                <div class="card p-3">
                    <div class="card-body">
                        <table id="learners_table" class="table">
                            <thead>
                            <tr>
                                <th>{{ __('Name')}}</th>
                                <th>{{ __('School')}}</th>
                                <th>{{ __('Grade')}}</th>
                                <th>{{ __('Stream')}}</th>
                                <th>{{ __('Status')}}</th>
                                <th>{{ __('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- push external js -->
    @push('script')
        <script src="{{ asset('plugins/select2/dist/js/select2.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/Cell-edit/dataTables.cellEdit.js') }}"></script>
        <script src="{{ asset('plugins/datedropper/datedropper.min.js') }}"></script>
        <!--server side permission table script-->
        <script src="{{ asset('js/learners.js') }}"></script>
    @endpush
@endsection
