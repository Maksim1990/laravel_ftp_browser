<script src="{{custom_asset('plugins/vendor/jstree/dist/jstree.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.js"></script>
<script>
    var token = '{{\Illuminate\Support\Facades\Session::token()}}';
    //-- Functionality to delete folder
    $('#delete_folder').click(function () {
        var folderId = $('#folder_id').val();
        var folderPath = $('#folder_path').val();

        var url = '{{ route('ajax_delete_folder') }}';
        var conf = confirm('{{trans('messages.want_delete_folder')}}');
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
                            text: '{{trans('messages.folder_deleted')}}'
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
        var conf = confirm('{{trans('messages.want_delete_file')}}');
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
                            text: '{{trans('messages.file_deleted')}}'
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
    $('.save_folder_name,.save_folder_name_root').click(function () {
        var buttonId = $(this).attr('id');
        if (buttonId === 'save') {
            var folderId = $('#folder_id').val();
            var folderPath = $('#folder_path').val();;
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

                        new Noty({
                            type: 'success',
                            layout: 'topRight',
                            text: '{{trans('messages.folder_created')}}'
                        }).show();

                    } else {
                        new Noty({
                            type: 'error',
                            layout: 'bottomLeft',
                            text: dataAjax['error']
                        }).show();
                    }

                    //-- Make folder input empty
                    $('#new_folder_name').val('');

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
                text: '{{trans('messages.folder_cant_be_empty')}}'
            }).show();
        }


    });


    //-- Functionality to toggle create new folder name
    $('#create_folder').click(function () {
        $('#create_folder_name').toggle();
        $('#upload_file_form').hide();
    });
    $('#create_folder_root').click(function () {
        $('#create_folder_name_root').toggle();
        $('#upload_file_form').hide();
    });

    $('#upload_file,#upload_file_root').click(function () {
        var folderName = $(this).data('folder');

        if (folderName === 'root') {
            var folderPath = $(this).data('path');
            var folderId = $(this).data('id');
            $('#hidden_folder_path').val(folderPath);
            $('#hidden_folder_id').val(folderId);

            //-- Hide right side block contents
            $('#file_block,#folder_block').hide();
        }

        $('#upload_file_form').toggle();
        $('#create_folder_name,#create_folder_name_root').hide();
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

            $("div#divLoading").addClass('show');

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
                            $('#delete_folder,#upload_file,.save_folder_name,.save_folder_name_root').attr('data-folder', data.instance.get_node(data.selected[0]).text);
                            $('#delete_folder,#upload_file,.save_folder_name,.save_folder_name_root').attr('data-id', data.instance.get_node(data.selected[0]).id);
                            $('#delete_folder,#upload_file,.save_folder_name,.save_folder_name_root').attr('data-path', data.instance.get_node(data.selected[0]).data);
                            $('#hidden_folder_path').val(data.instance.get_node(data.selected[0]).data);
                            $('#hidden_folder_id').val(data.instance.get_node(data.selected[0]).id);


                            $('#folder_name').val(data.instance.get_node(data.selected[0]).text);
                            $('#folder_id').val(data.instance.get_node(data.selected[0]).id);
                            $('#folder_path').val(data.instance.get_node(data.selected[0]).data);

                            if (dataAjax['arrFolderData'] !== null) {
                                $('#folder_name_info').text(dataAjax['arrFolderData']['folder_name']);
                                $('#folder_folders_info').text(dataAjax['arrFolderData']['folders']);
                                $('#folder_files_info').text(dataAjax['arrFolderData']['files']);
                            }

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
                var strLink = route('download_file', {file: data.instance.get_node(data.selected[0]).text});
                $('#download_file').attr('href', strLink);

                var arrData = getFileData(data.instance.get_node(data.selected[0]).text, data.instance.get_node(data.selected[0]).data + '/' + data.instance.get_node(data.selected[0]).text);
                if (arrData !== null) {
                    var arrDate = arrData['last_modified']['date'].split(" ");
                    $('#file_last_modified_info').text(arrDate[0]);
                    $('#file_name_info').text(arrData['name']);
                    $('#file_size_info').text(arrData['size'] + ' bytes');
                    $('#file_folder_info').text(arrData['folder']);
                }
                $("div#divLoading").removeClass('show');
            }
        }).jstree({
            'core': {
                'data': {!! $arrData !!},
                'check_callback': true
            }
        });
    });

    function getFileData(fileName, filePath) {
        var url = '{{ route('ajax_file_data') }}';
        var arrDetails = [];

        $.ajax({
            method: 'POST',
            url: url,
            async: false,
            dataType: "json",
            data: {
                fileName: fileName,
                filePath: filePath,
                _token: token
            },
            success: function (dataAjax) {

                if (dataAjax['result'] === "success") {
                    arrDetails = dataAjax['arrData'];
                }

                //-- Hide new folder name block
                $('#create_folder_name,#create_folder_name_root').hide();
                //-- Hide loading image

            }
        });

        return arrDetails;
    }


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
        $('#jstree').jstree('hide_node', $('#' +node));
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
                $('#upload_file_form').hide();

                //-- Show file loader spinner
                $('#file_loader').show();
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
                    text: '{{trans('messages.file_uploaded')}}'
                }).show();
            } else {
                new Noty({
                    type: 'error',
                    layout: 'bottomLeft',
                    text: response['error']
                }).show();
            }

            //-- Hide file loader spinner
            $('#file_loader').hide();
        }
    };
</script>