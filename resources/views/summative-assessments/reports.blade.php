@extends('layouts.main')
@section('title', 'Summative assessments Reports')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <div class="d-inline">
                            <h5>{{ __('Summative Assessments Reports')}}</h5>
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
                                <a href="#">{{ __('Summative Assessments Reports')}}</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- end message area-->
        <!-- only those have manage_permission permission will get access -->
        <div class="row clearfix">
            <!-- start message area-->
            @include('include.message')
            <div class="card">
                <div class="card-body assessment-details">
                    <form action="{{ route('summative-reports.generate-report') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label for="strand">{{ __('Class') }}</label>
                                    <select name="class_id" id="class_id" class="form-control select2">
                                        <option value="">{{ __('Select Class') }}</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->class }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label for="sub-strand">{{ __('Stream') }}</label>
                                    <select name="stream_id" disabled id="stream-id" class="form-control select2">
                                        <option value="">{{ __('Select Stream') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Terms') }}</label>
                                    <select name="term_id" required id="term_id" class="form-control select2">
                                        <option value="" selected>{{ __('Select Term') }}</option>
                                        @foreach($terms as $term)
                                            <option value="{{ $term->id }}">
                                                {{ $term->term }} - {{ $term->year }}
                                                ({{ \Carbon\Carbon::parse($term->start_date)->format('d M, Y') }}
                                                - {{ \Carbon\Carbon::parse($term->end_date)->format('d M, Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Assessments') }}</label>
                                    <select name="exam_id" required id="exam_id" disabled class="form-control select2">
                                        <option value="" selected>{{ __('Select Assessment') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Subjects') }}</label>
                                    <select name="subject_id" required id="subject_id" disabled
                                            class="form-control select2">
                                        <option value="" selected>{{ __('Select Subject') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4 text-right d-none" id="generate-report-btn">
                                <div class="form-group">
                                    <label for="">&nbsp;</label>
                                    <button class="btn btn-primary btn-rounded"
                                            type="submit">{{ __('Generate Class Report Card') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row" id="show_table" style="display: none">
            <div class="col-md-12">
                <div class="card p-3">
                    <form action="{{ route('summative-reports.bulk-download-pdf') }}" data-parsley-validate
                          id="summative-form"
                          method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <table id="learners_table" class="table">
                                        <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="all-summative-assessment">
                                            </th>
                                            <th>{{ __('Admission Number')}}</th>
                                            <th>{{ __('Name')}}</th>
                                            <th>{{ __('Score')}}</th>
                                            <th>{{ __('Remark')}}</th>
                                            <th>{{ __('Action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-12 text-right">
                                    <button type="button" class="btn btn-primary btn-rounded" disabled  id="generate-all-summative-report">{{ __('Print Selected Reports') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('script')
        <script src="{{ asset('js/summative-assessment.js') }}"></script>
    @endpush
@endsection
