@extends('layouts.main')
@section('title', 'Summative Board Sheet')
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
                        <div class="d-inline">
                            <h5>{{ __('Summative Board Sheet')}}</h5>
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
                                <a href="#">{{ __('Summative Board Sheet')}}</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <!-- start message area-->
            @include('include.message')
            @can('manage_summative_sheet')
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header"><h3>{{ __('Generate Summative Board Sheet')}}</h3></div>
                        <div class="card-body">
                            <form class="forms-sample" method="POST" data-parsley-validate
                                  action="{{ route('summative-board-sheet.generate-reports') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label for="class">{{ __('Class')}}<span class="text-red">*</span></label>
                                            <select name="class_id" id="class_id" class="form-control select2">
                                                <option value="">{{ __('Select Grade') }}</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->class }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label for="stream_id">{{ __('Stream')}}<span
                                                    class="text-red">*</span></label>
                                            <select name="stream_ids[]" disabled id="stream_id"
                                                    class="form-control select2" multiple>
                                                <option value="">{{ __('Select Stream') }}</option>
                                            </select>
                                        </div>
                                        <div class="form-check mx-2">
                                            <label class="custom-control custom-checkbox">
                                                <input type="checkbox" value="1" name="all_streams"
                                                       class="custom-control-input" id="all_streams" disabled>
                                                <span class="custom-control-label">&nbsp; {{ __('All Streams') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label for="title">{{ __('Year')}}<span class="text-red">*</span></label>
                                            <select required name="year" id="year" class="form-control select2">
                                                <option value="">{{ __('Select Year') }}</option>
                                                @for($i = \Carbon\Carbon::now()->format('Y'); $i >= \Carbon\Carbon::now()->format('Y') - 30; $i--)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label for="term_id">{{ __('Term')}}<span class="text-red">*</span></label>
                                            <select required disabled name="term_id" id="term_id"
                                                    class="form-control select2">
                                                <option value="">{{ __('Select Term') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label for="exam_ids">{{ __('Exams')}}<span
                                                    class="text-red">*</span></label>
                                            <select multiple required disabled name="exam_ids[]" id="exam_id"
                                                    class="form-control select2">
                                                <option value="">{{ __('Select Exams') }}</option>
                                            </select>
                                            <span
                                                class="text-danger">{{ __('Please select at least 1 exams and maximum 4') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 text-right">
                                        <div class="form-group">
                                            <button type="submit"
                                                    disabled
                                                    id="generate-reports-btn"
                                                    class="btn btn-success btn-rounded">{{ __('Generate Reports')}}</button>
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
        <!--server side permission table script-->
        <script src="{{ asset('js/summative-board-sheet.js') }}"></script>
    @endpush
@endsection
