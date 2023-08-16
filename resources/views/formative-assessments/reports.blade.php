@extends('layouts.main')
@section('title', 'Formative assessments Reports')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <div class="d-inline">
                            <h5>{{ __('Formative Assessments Reports')}}</h5>
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
                                <a href="#">{{ __('Formative Assessments Reports')}}</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- end message area-->
        <!-- only those have manage_permission permission will get access -->
        <form action="{{ route('reports.bulk-download-pdf') }}" data-parsley-validate method="post">
            @csrf
            <div class="row clearfix">
                <!-- start message area-->
                @include('include.message')
                <div class="card">
                    <div class="card-body assessment-details">
                        <div class="row">
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label for="strand">{{ __('Grade') }}</label>
                                    <select name="class_id" id="class_id" class="form-control select2" required>
                                        <option value="">{{ __('Select Grade') }}</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->class }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label for="sub-strand">{{ __('Stream') }}</label>
                                    <select name="stream_id" required disabled id="stream-id" class="form-control select2">
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
                            <div class="col-12 text-right">
                                <button type="submit" class="btn btn-rounded btn-primary"
                                        id="generate-all-formative-report" disabled>{{ __('Generate Bulk Reports') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="show_table" style="display: none">
                <div class="col-md-12">
                    <div class="card p-3">
                        <div class="card-body">
                            <table id="learners_table" class="table">
                                <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="all-formative-assessment">
                                    </th>
                                    <th>{{ __('Name')}}</th>
                                    <th>{{ __('Email')}}</th>
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
        </form>
    </div>
    @push('script')
        <script src="{{ asset('js/assessments-reports.js') }}"></script>
    @endpush
@endsection
