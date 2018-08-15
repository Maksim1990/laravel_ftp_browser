@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-6  col-xs-12">
            <div class="col-sm-12 col-md-12  col-xs-12">
                <h3 class="title">@lang('messages.ftp_connection')</h3>
                <div id="title_shape"></div>

                {!! Form::open(['method'=>'POST','action'=>['FtpBrowserController@storeFTPCredentials','userId'=>Auth::id()], 'files'=>true])!!}
                <div class="group-form">
                    {!! Form::label('ftp_host',trans('messages.ftp_host').':') !!}
                    {!! Form::text('ftp_host', Auth::user()->setting->ftp_host, ['class'=>'form-control']) !!}
                </div>

                <div class="group-form">
                    {!! Form::label('ftp_user_name',trans('messages.ftp_user_name').':') !!}
                    {!! Form::text('ftp_user_name', Auth::user()->setting->ftp_user_name, ['class'=>'form-control']) !!}
                </div>

                <div class="group-form">
                    {!! Form::label('ftp_password',trans('messages.ftp_password').':') !!}
                    {!! Form::text('ftp_password', Auth::user()->setting->ftp_password, ['class'=>'form-control']) !!}
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
    </div>
@stop
@section('scripts')
    <script>
        @if(Session::has('ftp_change'))
        new Noty({
            type: 'error',
            layout: 'bottomLeft',
            text: '{{session('ftp_change')}}'

        }).show();
        @endif
    </script>
@endsection
