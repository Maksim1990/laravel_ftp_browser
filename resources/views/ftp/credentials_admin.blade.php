@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-6  col-xs-12">
            <div class="col-sm-12 col-md-12  col-xs-12">
                <h3 class="title">@lang('messages.ftp_admin_connection')</h3>
                <div id="title_shape"></div>

                {!! Form::open(['method'=>'POST','action'=>['FtpBrowserController@storeFTPCredentialsAdmin','userId'=>Auth::id()], 'files'=>true])!!}
                <div class="group-form">
                    {!! Form::label('ftp_host',trans('messages.ftp_host').':') !!}
                    {!! Form::text('ftp_host', env('FTP_HOST'), ['class'=>'form-control']) !!}
                </div>

                <div class="group-form">
                    {!! Form::label('ftp_user_name',trans('messages.ftp_user_name').':') !!}
                    {!! Form::text('ftp_user_name', env('FTP_USER_NAME'), ['class'=>'form-control']) !!}
                </div>

                <div class="group-form">
                    {!! Form::label('ftp_password',trans('messages.ftp_password').':') !!}
                    {!! Form::text('ftp_password', env('FTP_PASS'), ['class'=>'form-control']) !!}
                </div>

                <div class="col-sm-12">
                    <br>
                    <a href="{{route("office_ftp_manager",Auth::id())}}" class="btn btn-warning">@lang('messages.ftp_bach_to_manager')</a>
                    {!! Form::submit(trans('messages.save'),['class'=>'btn btn-success']) !!}
                </div>

                {!! Form::close() !!}
            </div>

        </div>
        <div class="col-sm-12 col-md-12  col-xs-12">
            <br>
            @include('includes.formErrors')
        </div>

        {{-- Use admin FTP credentials--}}
        <div class="col-sm-12 col-lg-12 col-xs-12">
            <div class="col-sm-5 col-xs-12">
                <p class="text">@lang('messages.use_admin_credentials')</p>

            </div>
            <div class="col-sm-5 hidden-lg hidden-sm col-xs-12">
            </div>
            <div class="col-sm-1 col-xs-12">
                <div class="form-group" style="margin-top: 15px;">
                    @php
                        $strChecked="";
                        if(Auth::user()->admin_setting->use_admin_ftp_credentials=='Y'){
                         $strChecked="checked";
                        }
                    @endphp
                    <div class="material-switch pull-right">
                        <input id="admin_frp_credentials" name="admin_frp_credentials"
                               type="checkbox" {{$strChecked}}/>
                        <label for="admin_frp_credentials" class="label-success"></label>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-xs-12">
                <hr>
            </div>
        </div>

    </div>
@stop
@section('scripts')
    <script>
        @if(Session::has('ftp_change'))
        new Noty({
            type: 'success',
            layout: 'topRight',
            text: '{{session('ftp_change')}}'

        }).show();
        @endif

        var token = '{{\Illuminate\Support\Facades\Session::token()}}';
        //-- Functionality to update FTP connection type
        $('#admin_frp_credentials').click(function () {
            var url = '{{ route('ajax_admin_frp_credentials') }}';
            var admin_frp_credentials = "N";

            if ($(this).is(":checked"))
            {
                admin_frp_credentials="Y";
            }

            $.ajax({
                method: 'POST',
                url: url,
                dataType: "json",
                data: {
                    admin_frp_credentials: admin_frp_credentials,
                    _token: token
                }, beforeSend: function () {
                    //-- Show loading image while execution of ajax request
                    $("div#divLoading").addClass('show');
                },
                success: function (data) {
                    if (data['result'] === "success") {
                        new Noty({
                            type: 'success',
                            layout: 'topRight',
                            text: '{{trans('messages.admin_settings_option_updated')}}!'
                        }).show();
                    }
                    //-- Hide loading image
                    $("div#divLoading").removeClass('show');
                }
            });
        });

    </script>
@stop