@extends('layouts.master')
@section('styles')

@stop
@section('scripts_header')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{custom_asset('plugins/vendor/jstree/dist/themes/default/style.css')}}">
@stop
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-10  col-xs-12">
            <div id="browser_header" class="w3-center">
                <h3 class="title">@lang('messages.ftp_browser')</h3>
                <div id="title_shape"></div>
                <div class="insp_buttons">
                    <a href="{{route("office_ftp_manager",Auth::id())}}"
                       class="btn btn-warning">@lang('messages.ftp_bach_to_manager')</a>
                    <a href="#" id="close_all" class="btn btn-info">@lang('messages.close_all_nodes')</a>
                    <a href="#" id="create_folder_root" class="btn btn-success">@lang('messages.create_folder_name_root')</a>
                    <a href="#" id="upload_file_root" data-folder="root" data-path="" data-id=""
                       class="btn btn-success">@lang('messages.upload_file_root')</a>
                    <div id="create_folder_name_root" style="display: none;">
                        <input type="text" id="new_folder_name_root" class="form-control w3-margin-top"
                               style="width: 70%;display: inline;">
                        <a href="#" id="save_root" class="save_folder_name btn btn-success">@lang('messages.save')</a>
                    </div>
                    <div id="upload_file_form" style="display: none;">
                        {!! Form::open(['method'=>'POST','action'=>['FtpBrowserController@uploadFile','userId'=>Auth::id()],'id'=>'uploadForm', 'class'=>'dropzone'])!!}

                        {{ Form::hidden('folder_path', "",['id'=>'hidden_folder_path'] ) }}
                        {{ Form::hidden('folder_id', "",['id'=>'hidden_folder_id'] ) }}
                        {!! Form::close() !!}
                    </div>
                    <div id="file_loader" class="w3-center w3-margin-top" style="display: none;">
                        <div class="loader"></div>
                    </div>
                    <input type="hidden" id="folder_path" value="">
                    <input type="hidden" id="folder_name" value="">
                    <input type="hidden" id="folder_id" value="">
                </div>
            </div>

        </div>
        <div class="col-sm-8 col-sm-offset-2 col-md-10  col-xs-12">
            <div class="col-sm-6 col-md-6  col-xs-12">
                <div id="jstree"></div>
            </div>
            <div class="col-sm-6 col-md-6  col-xs-12">
                <div id="folder_block">
                    <a href="#" class="btn btn-danger" id="delete_folder" data-folder="">@lang('messages.delete_folder')</a>
                    <a href="#" class="btn btn-warning" id="create_folder" data-folder="">@lang('messages.create_folder')</a>
                    <a href="#" class="btn btn-info" id="upload_file" data-folder="">@lang('messages.upload_file')</a>
                    <div id="create_folder_name" style="display: none;">
                        <input type="text" id="new_folder_name" class="form-control w3-margin-top"
                               style="width: 70%;display: inline;">
                        <a href="#" id="save" class="save_folder_name btn btn-success">@lang('messages.save')</a>
                        <a href="#" id="cancel_folder_name" class="btn btn-warning">@lang('messages.cancel')</a>
                    </div>
                    <div class="w3-container">
                        <table class="w3-table w3-striped">
                            <tr>
                                <td><b>@lang('messages.folder_name'):</b></td>
                                <td id="folder_name_info"></td>
                            </tr>
                            <tr>
                                <td><b>@lang('messages.quantity_folders'): </b></td>
                                <td id="folder_folders_info"></td>
                            </tr>
                            <tr>
                                <td><b>@lang('messages.quantity_files'): </b></td>
                                <td id="folder_files_info"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div id="file_block">
                    <a href="#" class="btn btn-danger" id="delete_file">@lang('messages.delete_file')</a>
                    <a href="#" class="btn btn-success" id="download_file">@lang('messages.download_file')</a>

                    <div class="w3-container">
                        <table class="w3-table w3-striped">
                            <tr>
                                <td><b>@lang('messages.file_name'):</b></td>
                                <td id="file_name_info"></td>
                            </tr>
                            <tr>
                                <td><b>@lang('messages.size'): </b></td>
                                <td id="file_size_info"></td>
                            </tr>
                            <tr>
                                <td><b>@lang('messages.parent_folder'): </b></td>
                                <td id="file_folder_info"></td>
                            </tr>
                            <tr>
                                <td><b>@lang('messages.last_modified'): </b></td>
                                <td id="file_last_modified_info"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>
@stop
@section('scripts')
   @include('ftp.scripts')
@stop
