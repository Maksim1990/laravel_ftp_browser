@extends('layouts.master')
@section('styles')
    <style>
        #file_block, #folder_block {
            display: none;
            position: fixed;
            border-radius: 20px;
            border: 2px solid grey;
            padding: 20px 20px;
            top:220px;
        }
        #browser_header{
            position: fixed;
            background-color: white;
            z-index: 200;
            min-height: 200px;
            margin-left: 10%;
            padding: 20px 20px;
            border-radius: 30px;box-shadow: 5px 10px #888888;

        }
        #jstree{
            margin-top: 220px;
        }
    </style>
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
                    <a href="#" id="create_folder_root" class="btn btn-success">Create folder in the root folder</a>
                    <a href="#" id="upload_file_root" data-folder="root" data-path="" data-id=""
                       class="btn btn-success">Upload file in the root folder</a>
                    <div id="create_folder_name_root" style="display: none;">
                        <input type="text" id="new_folder_name_root" class="form-control w3-margin-top"
                               style="width: 70%;display: inline;">
                        <a href="#" id="save_root" class="save_folder_name btn btn-success">Save</a>
                    </div>
                    <div id="upload_file_form" style="display: none;">
                        {!! Form::open(['method'=>'POST','action'=>['FtpBrowserController@uploadFile','userId'=>Auth::id()],'id'=>'uploadForm', 'class'=>'dropzone'])!!}

                        {{ Form::hidden('folder_path', "",['id'=>'hidden_folder_path'] ) }}
                        {{ Form::hidden('folder_id', "",['id'=>'hidden_folder_id'] ) }}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-8 col-sm-offset-2 col-md-10  col-xs-12">
            <div class="col-sm-6 col-md-6  col-xs-12">
                <div id="jstree"></div>
            </div>
            <div class="col-sm-6 col-md-6  col-xs-12">
                <div id="folder_block">
                    <a href="#" class="btn btn-danger" id="delete_folder" data-folder="">Delete folder</a>
                    <a href="#" class="btn btn-warning" id="create_folder" data-folder="">Create folder</a>
                    <a href="#" class="btn btn-info" id="upload_file" data-folder="">Upload file</a>
                    <div id="create_folder_name" style="display: none;">
                        <input type="text" id="new_folder_name" class="form-control w3-margin-top"
                               style="width: 70%;display: inline;">
                        <a href="#" id="save" class="save_folder_name btn btn-success">Save</a>
                        <a href="#" id="cancel_folder_name" class="btn btn-warning">Cancel</a>
                    </div>

                </div>
                <div id="file_block">
                    <a href="#" class="btn btn-danger" id="delete_file">Delete file</a>
                    <a href="#" class="btn btn-success" id="download_file">Download file</a>
                </div>
            </div>

        </div>

    </div>
@stop
@section('scripts')
    <script src="{{custom_asset('plugins/vendor/jstree/dist/jstree.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.js"></script>
    <script>
        var token = '{{\Illuminate\Support\Facades\Session::token()}}';
        //-- Functionality to delete folder
        $('#delete_folder').click(function () {
            var folderId = $(this).data('id');
            var folderPath = $(this).data('path');

            var url = '{{ route('ajax_delete_folder') }}';
            var conf = confirm('Do you really want to delete this folder?');
            if (conf) {
                $.ajax({
                    method: 'POST',
                    url: url,
                    dataType: "json",
                    data: {
                        folderPath: folderPath,
                        _token: token
                    }, beforeSend: function () {
                        //-- Show loading image while execution of ajax request
                        $("div#divLoading").addClass('show');
                    },
                    success: function (dataAjax) {
                        if (dataAjax['result'] === "success") {
                            deleteNode(folderId);
                            new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Folder successfully deleted!'
                            }).show();
                        }
                        //-- Hide right side block contents
                        $('#file_block,#folder_block').hide();
                        //-- Hide loading image
                        $("div#divLoading").removeClass('show');
                    }
                });
            }
        });


        //-- Functionality to delete file
        $('#delete_file').click(function () {
            var fileId = $(this).data('id');
            var filePath = $(this).data('path');

            var url = '{{ route('ajax_delete_file') }}';
            var conf = confirm('Do you really want to delete this file?');
            if (conf) {
                $.ajax({
                    method: 'POST',
                    url: url,
                    dataType: "json",
                    data: {
                        filePath: filePath,
                        _token: token
                    }, beforeSend: function () {
                        //-- Show loading image while execution of ajax request
                        $("div#divLoading").addClass('show');
                    },
                    success: function (dataAjax) {
                        if (dataAjax['result'] === "success") {
                            deleteNode(fileId);
                            new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'File successfully deleted!'
                            }).show();
                        }

                        //-- Hide file block
                        $('#file_block').hide();
                        //-- Hide right side block contents
                        $('#file_block,#folder_block').hide();
                        //-- Hide loading image
                        $("div#divLoading").removeClass('show');
                    }
                });
            }
        });


        //-- Functionality to create folder
        $('.save_folder_name').click(function () {
            var buttonId = $(this).attr('id');
            if (buttonId === 'save') {
                var folderId = $(this).data('id');
                var folderPath = $(this).data('path');
                var folderNewName = $('#new_folder_name').val();
            } else {
                var folderId = 'root';
                var folderPath = '';
                var folderNewName = $('#new_folder_name_root').val();
            }

            var url = '{{ route('ajax_create_folder') }}';

            if (folderNewName !== "") {
                $.ajax({
                    method: 'POST',
                    url: url,
                    dataType: "json",
                    data: {
                        folderId: folderId,
                        folderNewName: folderNewName,
                        folderPath: folderPath,
                        _token: token
                    }, beforeSend: function () {
                        //-- Show loading image while execution of ajax request
                        $("div#divLoading").addClass('show');
                    },
                    success: function (dataAjax) {
                        if (dataAjax['result'] === "success") {

                            if (!dataAjax['root']) {
                                createNode("#" + folderId, dataAjax['arrData'][0], "last");
                            } else {
                                $('#jstree').jstree('create_node', "#", {
                                    "text": dataAjax['arrData'][0]['text'],
                                    "id": dataAjax['arrData'][0]['id'],
                                    "data": dataAjax['arrData'][0]['data'],
                                    "icon": dataAjax['arrData'][0]['icon']
                                }, 'last');
                            }

                        }

                        //-- Hide new folder name block
                        $('#create_folder_name,#create_folder_name_root').hide();
                        //-- Hide loading image
                        $("div#divLoading").removeClass('show');
                    }
                });
            } else {
                new Noty({
                    type: 'error',
                    layout: 'bottomLeft',
                    text: 'Folder name can\t be empty'
                }).show();
            }


        });


        //-- Functionality to toggle create new folder name
        $('#create_folder').click(function () {
            $('#create_folder_name').toggle();
        });
        $('#create_folder_root').click(function () {
            $('#create_folder_name_root').toggle();
        });

        $('#upload_file,#upload_file_root').click(function () {
            var folderName = $(this).data('folder');

            if (folderName === 'root') {
                var folderPath = $(this).data('path');
                var folderId = $(this).data('id');
                $('#hidden_folder_path').val(folderPath);
                $('#hidden_folder_id').val(folderId);
            }

            $('#upload_file_form').toggle();
        });

        //-- Functionality to toggle upload file form
        $('#cancel_folder_name').click(function () {
            $('#create_folder_name').toggle();
        });
        //-- Functionality to close all nodes
        $('#close_all').click(function () {
            closeAll();
            //-- Hide right side block contents
            $('#file_block,#folder_block').hide();
        });
        // Setup the jsTree.
        $(function () {
            $('#jstree').on("changed.jstree", function (e, data) {

                var arrId = data.instance.get_node(data.selected[0]).id.split('_');
                var fileType = arrId[arrId.length - 1];

                //-- Hide right side block contents
                $('#file_block,#folder_block,#upload_file_form').hide();


                if (fileType === 'folder') {

                    var url = '{{ route('ajax_get_folder_content') }}';
                    $.ajax({
                        method: 'POST',
                        url: url,
                        dataType: "json",
                        data: {
                            folder: data.instance.get_node(data.selected[0]).text,
                            id: data.instance.get_node(data.selected[0]).id,
                            path: data.instance.get_node(data.selected[0]).data,
                            _token: token
                        }, beforeSend: function () {
                            //-- Show loading image while execution of ajax request
                            $("div#divLoading").addClass('show');
                        },
                        success: function (dataAjax) {


                            if (dataAjax['result'] === "success") {

                                if (dataAjax['arrData'].length > 0) {

                                    //-- Check whether children nodes were already generated for this node
                                    if (data.instance.get_node(data.selected[0]).children.length <= 0) {

                                        //-- Generate new child nodes
                                        for (var i = 0; i < dataAjax['arrData'].length; i++) {
                                            createNode("#" + data.instance.get_node(data.selected[0]).id, dataAjax['arrData'][i], "last");
                                        }

                                        //-- Open node that was currently clicked
                                        openNode("#" + data.instance.get_node(data.selected[0]).id);
                                    } else {
                                        if (data.instance.get_node(data.selected[0]).state.opened) {
                                            //-- Close clicked node
                                            closeNode("#" + data.instance.get_node(data.selected[0]).id);
                                        } else {
                                            //-- Open clicked node
                                            openNode("#" + data.instance.get_node(data.selected[0]).id);
                                        }
                                    }

                                } else {
                                    new Noty({
                                        type: 'success',
                                        layout: 'topRight',
                                        text: '{{trans('messages.folder_empty')}}'
                                    }).show();
                                }


                                $('#folder_block').show();
                                $('#delete_folder,#upload_file,.save_folder_name').attr('data-folder', data.instance.get_node(data.selected[0]).text);
                                $('#delete_folder,#upload_file,.save_folder_name').attr('data-id', data.instance.get_node(data.selected[0]).id);
                                $('#delete_folder,#upload_file,.save_folder_name').attr('data-path', data.instance.get_node(data.selected[0]).data);
                                $('#hidden_folder_path').val(data.instance.get_node(data.selected[0]).data);
                                $('#hidden_folder_id').val(data.instance.get_node(data.selected[0]).id);

                            } else {
                                new Noty({
                                    type: 'error',
                                    layout: 'bottomLeft',
                                    text: dataAjax['error']
                                }).show();
                                $('#file_block').show();
                            }
                            //-- Hide loading image
                            $("div#divLoading").removeClass('show');
                        }
                    });
                } else {
                    $('#file_block').show();
                    $('#delete_file,#download_file').attr('data-file', data.instance.get_node(data.selected[0]).text);
                    $('#delete_file,#download_file').attr('data-id', data.instance.get_node(data.selected[0]).id);
                    $('#delete_file,#download_file').attr('data-path', data.instance.get_node(data.selected[0]).data + '/' + data.instance.get_node(data.selected[0]).text);
                    var strLink=route('download_file',{file:data.instance.get_node(data.selected[0]).text});
                    $('#download_file').attr('href',strLink);
                }

            }).jstree({
                'core': {
                    'data': {!! $arrData !!},
                    'check_callback': true
                }
            });
        });


        // $('#jstree').on("hover_node.jstree", function (e, data) {
        //
        //    console.log(data.node.text);
        // });


        //-- Functionality when create new node
        function createNode(parent_node, data, position) {
            $('#jstree').jstree('create_node', $(parent_node), {
                "text": data['text'],
                "id": data['id'],
                "data": data['data'],
                "icon": data['icon']
            }, position, false, false);
        }

        //-- Functionality when open specific node
        function openNode(parent_node) {
            $('#jstree').jstree('open_node', $(parent_node));
        }

        //-- Functionality when hide specific node
        function deleteNode(node) {
            $('#' + node).hide();
        }

        //-- Functionality when close all nodes
        function closeAll() {
            $('#jstree').jstree('close_all');
        }

        //-- Functionality when close specific node
        function closeNode(parent_node) {
            $('#jstree').jstree('close_node', $(parent_node));
        }

        Dropzone.options.uploadForm = {
            init: function () {
                this.on("sending", function (file) {
                    alert('Sending the file' + file.name)
                });
                this.on("complete", function (file) {
                    this.removeFile(file);
                });
            },
            dataType: "json",
            success: function (file, response) {
                if (response['result'] === "success") {

                    if (response['folderId'] !== 'root') {
                        createNode("#" + response['folderId'], response['arrData'][0], "last");
                    } else {
                        $('#jstree').jstree('create_node', "#", {
                            "text": response['arrData'][0]['text'],
                            "id": response['arrData'][0]['id'],
                            "data": response['arrData'][0]['data'],
                            "icon": response['arrData'][0]['icon']
                        }, 'last');
                    }


                    //-- Toggle upload file form
                    $('#upload_file_form').toggle();

                    new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'File successfully updated!'
                    }).show();
                } else {
                    new Noty({
                        type: 'error',
                        layout: 'bottomLeft',
                        text: response['error']
                    }).show();
                }
            }
        };
    </script>
@stop
