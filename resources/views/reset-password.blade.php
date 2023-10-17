@extends('layouts.main')
@section('title', 'Profile')
@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <div class="d-inline">
                            <h5>{{ __('Reset Password')}}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{route('dashboard')}}"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Reset Password')}}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row align-items-end">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            @include('include.message')
                            <form action="{{ route('change-password') }}" method="post" data-parsley-validate>
                                @csrf
                                <div class="row justify-content-center">
                                    <div class="col-8">
                                        <div class="form-group">
                                            <label for="">{{ __('Old Password') }}</label>
                                            <input type="password" class="form-control" name="old_password" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="">{{ __('New Password') }}</label>
                                            <input type="password" class="form-control" name="new_password" id="new_password" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="">{{ __('Confirm Password') }}</label>
                                            <input type="password" class="form-control" data-parsley-equalto="#new_password" name="confirm_password" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-right">
                                        <button type="button" class="btn btn-primary btn-rounded" data-toggle="modal" data-target="#resetPasswordModal">{{ __('Send Reset Password Link') }}</button>
                                        <button type="submit" class="btn btn-success btn-rounded">{{ __('Change Password') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
