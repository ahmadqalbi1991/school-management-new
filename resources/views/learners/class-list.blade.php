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
                            <h5>{{ __('Class List')}}</h5>
                            <span>{{ __('Download Class Learners')}}</span>
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
                                <a href="#">{{ __('Class List')}}</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            @include('include.message')
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="">{{ __('Grade') }}</label>
                                <select name="class_id" id="class_id" class="form-control select2">
                                    <option value="">{{ __('Select Grade') }}</option>
                                    @foreach($classes as $class)
                                        <option
                                            value="{{ $class->id }}">{{ $class->class }} @if(Auth::user()->role === 'super_admin')
                                                ({{ $class->school->school_name }})
                                            @endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="">{{ __('Stream') }}</label>
                                <select name="stream_id[]" id="stream-id" class="form-control select2" multiple>
                                    <option value="">{{ __('Select Stream') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 text-right">
                            <button class="btn btn-danger btn-rounded btn-sm" id="pdf-btn"><i
                                    class="fas fa-file-pdf"></i> {{ __('PDF') }}</button>
                            <button class="btn btn-primary btn-rounded btn-sm" id="search-btn"><i
                                    class="fas fa-search"></i> {{ __('Search') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card p-3">
                    <div class="card-body">
                        <table id="class_list_table" class="table">
                            <thead>
                            <tr>
                                @if(Auth::user()->role === 'super_admin')
                                    <th>{{ __('School')}}</th>
                                @endif
                                <th>{{ __('Grade')}}</th>
                                <th>{{ __('Stream')}}</th>
                                <th>{{ __('Learner')}}</th>
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
