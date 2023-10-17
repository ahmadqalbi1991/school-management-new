@extends('layouts.main')
@section('title', 'Profile')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <div class="d-inline">
                            <h5>{{ __('Profile')}}</h5>
                            <span>{{ $user->email }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{route('dashboard')}}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Profile')}}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-5">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <img
                                src="{{ getImage($user->profile_image) }}"
                                class="rounded-circle" width="150"/>
                            <h4 class="card-title mt-10">{{ $user->name }}</h4>
                            <p class="card-subtitle">{{ $user->tsc_number }}</p>
                            <div class="row text-center justify-content-md-center">
                                <div class="col-4"><a href="javascript:void(0)" class="link"><i class="ik ik-book"></i>
                                        <font class="font-medium">{{ $user->subjects->count() }}</font></a></div>
                                <div class="col-4"><a href="javascript:void(0)" class="link"><i class="ik ik-home"></i>
                                        <font class="font-medium">{{ $user->streams->count() }}</font></a></div>
                            </div>
                        </div>
                    </div>
                    <hr class="mb-0">
                    <div class="card-body">
                        <small class="text-muted d-block">{{ __('Email address')}} </small>
                        <h6>{{ $user->email }}</h6>
                        <small class="text-muted d-block pt-10">{{ __('Phone')}}</small>
                        <h6>{{ $user->phone_number }}</h6>
                        {{--                        <div class="map-box">--}}
                        {{--                            <iframe--}}
                        {{--                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d248849.886539092!2d77.49085452149588!3d12.953959988118836!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae1670c9b44e6d%3A0xf8dfc3e8517e4fe0!2sBengaluru%2C+Karnataka!5e0!3m2!1sen!2sin!4v1542005497600"--}}
                        {{--                                width="100%" height="300" frameborder="0" style="border:0" allowfullscreen></iframe>--}}
                        {{--                        </div>--}}
                        {{--                        <small class="text-muted d-block pt-30">{{ __('Social Profile')}}</small>--}}
                        {{--                        <br/>--}}
                        {{--                        <button class="btn btn-icon btn-facebook"><i class="fab fa-facebook-f"></i></button>--}}
                        {{--                        <button class="btn btn-icon btn-twitter"><i class="fab fa-twitter"></i></button>--}}
                        {{--                        <button class="btn btn-icon btn-instagram"><i class="fab fa-instagram"></i></button>--}}
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-7">
                <div class="card">
                    <ul class="nav nav-pills custom-pills" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-timeline-tab" data-toggle="pill" href="#current-month"
                               role="tab" aria-controls="pills-timeline" aria-selected="true">{{ __('Activities')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#last-month" role="tab"
                               aria-controls="pills-profile" aria-selected="false">{{ __('Profile')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-setting-tab" data-toggle="pill" href="#previous-month"
                               role="tab" aria-controls="pills-setting" aria-selected="false">{{ __('Setting')}}</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="current-month" role="tabpanel"
                             aria-labelledby="pills-timeline-tab">
                            <div class="card-body">
                                <div class="profiletimeline mt-0">
                                    @foreach($user->activities as $activity)
                                        <div class="sl-item">
                                            <div class="sl-left"><img src="{{ getImage($user->profile_image) }}"
                                                                      alt="user"
                                                                      class="rounded-circle"/></div>
                                            <div class="sl-right">
                                                <div><a href="javascript:void(0)"
                                                        class="link">{{ $user->id === $activity->user_id ? "Me" : $user->name }}</a>
                                                    <span
                                                        class="sl-date">{{ $activity->created_at->diffForHumans() }}</span>
                                                    <p>{{ $activity->activity }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="last-month" role="tabpanel" aria-labelledby="pills-profile-tab">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 col-6"><strong>{{ __('Full Name')}}</strong>
                                        <br>
                                        <p class="text-muted">{{ $user->name }}</p>
                                    </div>
                                    <div class="col-md-3 col-6"><strong>{{ __('Mobile')}}</strong>
                                        <br>
                                        <p class="text-muted">{{ $user->phone_number }}</p>
                                    </div>
                                    <div class="col-md-3 col-6"><strong>{{ __('Email')}}</strong>
                                        <br>
                                        <p class="text-muted">{{ $user->email }}</p>
                                    </div>
                                    <div class="col-md-3 col-6"><strong>{{ __('TCS Number')}}</strong>
                                        <br>
                                        <p class="text-muted">{{ $user->tcs_number }}</p>
                                    </div>
                                </div>
                                <hr>
                                <h5>{{ __('My Subjects') }}</h5>
                                <div class="col-12">
                                    <div class="row">
                                        @foreach($user->subjects as $subject)
                                            <div class="col-md-4 col-sm-12">
                                                <a href="javascript:void(0)">
                                                    <div class="class-wrapper">
                                                        <h3><i class="fas fa-book-open"></i></h3>
                                                        <p class="m-0">{{ $subject->subject->title }}</p>
                                                        <span>{{ $subject->stream->school_class->class }} ({{ $subject->stream->title }})</span>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <hr>
                                <h5>{{ __('My Classes') }}</h5>
                                <div class="col-12">
                                    <div class="row">
                                        @foreach($user->streams as $stream)
                                            <div class="col-md-4 col-sm-12">
                                                <a href="javascript:void(0)">
                                                    <div class="class-wrapper">
                                                        <h3><i class="fas fa-graduation-cap"></i></h3>
                                                        <p>{{ $stream->class->class }} - {{ $stream->stream->title }}</p>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="previous-month" role="tabpanel"
                             aria-labelledby="pills-setting-tab">
                            <div class="card-body">
                                <form action="{{ route('update-profile', ['id' => $user->id]) }}" enctype="multipart/form-data" method="post" class="form-horizontal">
                                    @csrf
                                    <div class="form-group">
                                        <label for="example-name">{{ __('Full Name')}}</label>
                                        <input type="text" disabled class="form-control" value="{{ $user->name }}"
                                               name="name" id="example-name">
                                    </div>
                                    <div class="form-group">
                                        <label for="example-email">{{ __('Email')}}</label>
                                        <input type="email" disabled value="{{ $user->email }}" class="form-control"
                                               name="email" id="example-email">
                                    </div>
                                    <div class="form-group">
                                        <label for="example-phone">{{ __('Phone No')}}</label>
                                        <input type="text" disabled value="{{ $user->phone_number }}" id="example-phone"
                                               name="phone_number" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="example-phone">{{ __('TSC Number')}}</label>
                                        <input type="text" disabled value="{{ $user->tsc_number }}" id="example-phone"
                                               name="tsc_number" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="logo">{{ __('Profile Image') }}</label>
                                        <input accept="image/*" type="file" class="form-control" name="profile_image">
                                    </div>
                                    <button class="btn btn-success" type="submit">Update Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
