<header class="header-top" header-theme="light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <div class="top-menu d-flex align-items-center">
                <button type="button" class="btn-icon mobile-nav-toggle d-lg-none"><span></span></button>

                <div class="header-search">
{{--                    <div class="input-group">--}}

{{--                        <span class="input-group-addon search-close">--}}
{{--                            <i class="ik ik-x"></i>--}}
{{--                        </span>--}}
{{--                        <input type="text" class="form-control">--}}
{{--                        <span class="input-group-addon search-btn"><i class="ik ik-search"></i></span>--}}
{{--                    </div>--}}
                </div>
                <!--<button class="nav-link" title="clear cache">
                    <a  href="{{url('clear-cache')}}">
                    <i class="ik ik-battery-charging"></i>
                </a>
                </button> &nbsp;&nbsp;
                <button type="button" id="navbar-fullscreen" class="nav-link"><i class="ik ik-maximize"></i></button>-->
            </div>
            <div class="top-menu d-flex align-items-center">
{{--                <div class="dropdown">--}}
{{--                    <a class="nav-link dropdown-toggle" href="#" id="notiDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ik ik-bell"></i><span class="badge bg-danger">3</span></a>--}}
{{--                    <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notiDropdown">--}}
{{--                        <h4 class="header">{{ __('Notifications')}}</h4>--}}
{{--                        <div class="notifications-wrap">--}}
{{--                            <a href="#" class="media">--}}
{{--                                <span class="d-flex">--}}
{{--                                    <i class="ik ik-check"></i>--}}
{{--                                </span>--}}
{{--                                <span class="media-body">--}}
{{--                                    <span class="heading-font-family media-heading">{{ __('Invitation accepted')}}</span>--}}
{{--                                    <span class="media-content">{{ __('Your have been Invited ...')}}</span>--}}
{{--                                </span>--}}
{{--                            </a>--}}
{{--                            <a href="#" class="media">--}}
{{--                                <span class="d-flex">--}}
{{--                                    <img src="{{ asset('img/users/1.jpg')}}" class="rounded-circle" alt="">--}}
{{--                                </span>--}}
{{--                                <span class="media-body">--}}
{{--                                    <span class="heading-font-family media-heading">{{ __('Steve Smith')}}</span>--}}
{{--                                    <span class="media-content">{{ __('I slowly updated projects')}}</span>--}}
{{--                                </span>--}}
{{--                            </a>--}}
{{--                            <a href="#" class="media">--}}
{{--                                <span class="d-flex">--}}
{{--                                    <i class="ik ik-calendar"></i>--}}
{{--                                </span>--}}
{{--                                <span class="media-body">--}}
{{--                                    <span class="heading-font-family media-heading">{{ __('To Do')}}</span>--}}
{{--                                    <span class="media-content">{{ __('Meeting with Nathan on Friday 8 AM ...')}}</span>--}}
{{--                                </span>--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                        <div class="footer"><a href="javascript:void(0);">{{ __('See all activity')}}</a></div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <button type="button" class="nav-link ml-10 right-sidebar-toggle"><i class="ik ik-message-square"></i><span class="badge bg-success">3</span></button>--}}
{{--                <div class="dropdown">--}}
{{--                    <a class="nav-link dropdown-toggle" href="#" id="menuDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ik ik-plus"></i></a>--}}
{{--                    <div class="dropdown-menu dropdown-menu-right menu-grid" aria-labelledby="menuDropdown">--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Dashboard"><i class="ik ik-bar-chart-2"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Message"><i class="ik ik-mail"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Accounts"><i class="ik ik-users"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Sales"><i class="ik ik-shopping-cart"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Purchase"><i class="ik ik-briefcase"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Pages"><i class="ik ik-clipboard"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Chats"><i class="ik ik-message-square"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Contacts"><i class="ik ik-map-pin"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Blocks"><i class="ik ik-inbox"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Events"><i class="ik ik-calendar"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="Notifications"><i class="ik ik-bell"></i></a>--}}
{{--                        <a class="dropdown-item" href="#" data-toggle="tooltip" data-placement="top" title="More"><i class="ik ik-more-horizontal"></i></a>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <button type="button" class="nav-link ml-10" id="apps_modal_btn" data-toggle="modal" data-target="#appsModal"><i class="ik ik-grid"></i></button>--}}
                <div class="dropdown">
                    <a class="dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="avatar" src="{{ getImage(Auth::user()->profile_image) }}" alt="">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
{{--                        <a class="dropdown-item" href="{{url('profile')}}"><i class="ik ik-user dropdown-icon"></i> {{ __('Profile')}}</a>--}}
{{--                        <a class="dropdown-item" href="#"><i class="ik ik-navigation dropdown-icon"></i> {{ __('Message')}}</a>--}}
                        <a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="fas fa-user dropdown-icon"></i>
                            {{ __('Profile')}}
                        </a>
{{--                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#resetPasswordModal">--}}
{{--                            <i class="fas fa-envelope dropdown-icon"></i>--}}
{{--                            {{ __('Reset Password')}}--}}
{{--                        </a>--}}
                        <a class="dropdown-item" href="{{ route('reset-password') }}">
                            <i class="fas fa-envelope dropdown-icon"></i>
                            {{ __('Reset Password')}}
                        </a>
                        <a class="dropdown-item" href="{{ url('logout') }}">
                            <i class="ik ik-power dropdown-icon"></i>
                            {{ __('Logout')}}
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</header>
<div class="modal fade show" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">{{ __('Send Reset Password Link') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <form method="POST" action="{{ route('password.email') }}" data-parsley-validate>
                @csrf
                <div class="modal-body">
                    <div class="authentication-form mx-auto">
                        <div class="row w-100">
                            <div class="col-12">
                                <div class="form-group">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Your email address" name="email" value="{{ old('email') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('Send Reset Password Link') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

