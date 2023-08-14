@extends('layouts.main')
@section('title', 'Learners Management')
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
                            <h5>{{ __('Learners Management')}}</h5>
                            <span>{{ __('Move learners from previous class to next class')}}</span>
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
                                <a href="#">{{ __('Learners Management')}}</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        @can('manage_learners')
            <form class="forms-sample" method="POST" action="{{ route('learners.move-learners') }}" data-parsley-validate>
                @csrf
                <div class="row clearfix">
                    <!-- start message area-->
                    @include('include.message')
                    <!-- end message area-->
                    <!-- only those have manage_permission permission will get access -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row w-100">
                                    <div class="col-md-6 col-sm-12">
                                        <h3>{{ __('Move Learner(s)')}}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <h5>{{ __('Previous Class Data') }}</h5>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Class') }}</label>
                                            <select id="class_id_previous" class="form-control select2">
                                                <option value="">{{ __('Select Class') }}</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->class }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Stream') }}</label>
                                            <select id="stream_id_previous" class="form-control select2">
                                                <option value="">{{ __('Select Stream') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" id="next-class-div" style="display: none;">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <h5>{{ __('Next Class Data') }}</h5>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Class') }}</label>
                                            <select id="class_id_next" class="form-control select2">
                                                <option value="">{{ __('Select Class') }}</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->class }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Stream') }}</label>
                                            <select id="stream_id_next" name="stream_id" class="form-control select2">
                                                <option value="">{{ __('Select Stream') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="learners-div" style="display: none">
                    <div class="col-md-12">
                        <div class="card p-3">
                            <div class="card-body">
                                <table id="learners_table" class="table">
                                    <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="all-learners">
                                        </th>
                                        <th>{{ __('Name')}}</th>
                                        <th>{{ __('Email')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 text-right">
                         <button class="btn btn-success btn-rounded" disabled id="submit-btn">{{ __('Move Learners') }}</button>
                    </div>
                </div>
            </form>
        @endcan
    </div>
    <!-- push external js -->
    @push('script')
        <script src="{{ asset('plugins/select2/dist/js/select2.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('plugins/DataTables/Cell-edit/dataTables.cellEdit.js') }}"></script>
        <script src="{{ asset('plugins/datedropper/datedropper.min.js') }}"></script>
        <!--server side permission table script-->
        <script src="{{ asset('js/learner-management.js') }}"></script>
    @endpush
@endsection
