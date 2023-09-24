@extends('layouts.main')
@section('title', 'Summative Performance Levels')
@section('content')
    <!-- push external head elements to head -->
    @push('head')
        <link rel="stylesheet" href="{{ asset('plugins/DataTables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet"
              href="{{ asset('plugins/tempusdominus-bootstrap-4/build/css/tempusdominus-bootstrap-4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/jquery-minicolors/jquery.minicolors.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datedropper/datedropper.min.css') }}">
    @endpush

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <div class="d-inline">
                            <h5>{{ __('Summative Performance Levels')}}</h5>
                            <span>{{ __('Summative Performance Levels Details')  }}</span>
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
                                <a href="#">{{ __('Summative Performance Levels')}}</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <!-- start message area-->
            @include('include.message')
            @can('manage_summative_performance_levels')
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header"><h3>{{ __('Add Level')}}</h3></div>
                        <div class="card-body">
                            <form class="forms-sample" method="POST" data-parsley-validate
                                  action="{{ empty($level) ? route('summative-performance-levels.store') : route('summative-performance-levels.update', ['id' => $level->id])}}">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="title">{{ __('Title')}}<span class="text-red">*</span></label>
                                            <input type="text" name="title" class="form-control" required
                                                   placeholder="Enter the title of level"
                                                   value="{{ !empty($level) ? $level->title : '' }}"
                                            >
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label for="min_points">{{ __('Minimum Points')}}<span class="text-red">*</span></label>
                                                    <input type="number" name="min_point" data-parsley-max="100"
                                                           value="{{ !empty($level) ? $level->min_point : $min }}" max="100"
                                                           class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label for="max_points">{{ __('Maximum Points')}}<span class="text-red">*</span></label>
                                                    <input type="number" name="max_point"
                                                           value="{{ !empty($level) ? $level->max_point : '0' }}" min="0" max="100"
                                                           class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="">{{ __('Teacher Remark') }} <span class="text-red">*</span></label>
                                                    <input type="text" required value="{{ !empty($level) ? $level->teacher_remark : '' }}" name="teacher_remark" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Details')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="detail" id="detail" rows="5" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250" placeholder="Details"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->detail : '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 1')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_1" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_1 : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 2')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_2" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_2 : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 3')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_3" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_3 : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 4')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_4" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_4 : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 5')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_5" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_5 : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 6')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_6" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_6 : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 7')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_7" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_7 : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 8')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_8" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_8 : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 9')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_9" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_9 : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="detail">{{ __('Comment 10')}}<span
                                                    class="text-red">*</span></label>
                                            <textarea name="comment_10" id="detail" rows="3" data-parsley-minlength="20"
                                                      data-parsley-maxlength="250"
                                                      class="form-control"
                                                      required>{{ !empty($level) ? $level->comment_10 : '' }}</textarea>
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
                        <table id="levels_tables" class="table">
                            <thead>
                            <tr>
                                <th>{{ __('Title')}}</th>
                                <th>{{ __('Points')}}</th>
                                <th>{{ __('Actions')}}</th>
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
        <script src="{{ asset('plugins/moment/moment.js') }}"></script>
        <script
            src="{{ asset('plugins/tempusdominus-bootstrap-4/build/js/tempusdominus-bootstrap-4.min.js') }}"></script>
        <script src="{{ asset('plugins/jquery-minicolors/jquery.minicolors.min.js') }}"></script>
        <script src="{{ asset('plugins/datedropper/datedropper.min.js') }}"></script>
        <!--server side permission table script-->
        <script src="{{ asset('js/performance-levels.js') }}"></script>
    @endpush
@endsection
