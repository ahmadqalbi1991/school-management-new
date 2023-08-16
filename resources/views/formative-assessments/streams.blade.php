@extends('layouts.main')
@section('title', 'Formative assessments')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <div class="d-inline">
                            <h5>{{ __('Formative Assessments')}}</h5>
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
                                <a href="#">{{ __('Formative Assessments')}}</a>
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body bg-facebook p-4">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="text-light">Select a Grade</h5>
                                <p class="text-light">Learners are organised into different grade. To assess a group of learners, you first need
                                    to select the grade in which they belong, and we will load the assessment sheet with the
                                    learners of that grade.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="row">
                    @foreach($streams as $stream)
                        <div class="col-md-3 col-sm-12">
                            <a href="{{ route('formative-assessments.index', ['class' =>  $stream->school_class->slug, 'stream' => $stream->slug]) }}">
                                <div class="class-wrapper">
                                    <h3><i class="fas fa-graduation-cap"></i></h3>
                                    <p>{{ $stream->school_class->class }} - {{ $stream->title }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="d-flex justify-content-center w-100 mt-5">
                {{ $streams->links() }}
            </div>
        </div>
    </div>
@endsection
