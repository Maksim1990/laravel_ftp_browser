@extends('layouts.master')
@section('styles')

@stop
@section('scripts_header')
    <link rel="stylesheet" href="{{custom_asset('plugins/vendor/jstree/dist/themes/default/style.css')}}">



@stop
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-10  col-xs-12">
            <div class="col-sm-12 col-md-12  col-xs-12">
                <h3 class="title">@lang('messages.ftp_browser')</h3>
                <div id="title_shape"></div>
                <div class="insp_buttons">
                    <a href="{{route("office_ftp_manager",Auth::id())}}" class="btn btn-warning">@lang('messages.ftp_bach_to_manager')</a>
                    <a href="#" id="close_all" class="btn btn-info">@lang('messages.close_all_nodes')</a>
                </div>
                <div id="jstree"></div>
                
            </div>

        </div>

    </div>
@stop
@section('scripts')
    <script src="{{custom_asset('plugins/vendor/jstree/dist/jstree.min.js')}}"></script>
    <script>
        //-- Functionality to close all nodes
        $('#close_all').click(function () {
            closeAll();
        });
        // Setup the jsTree.
        $(function () {
            $('#jstree').on("changed.jstree", function (e, data) {

                var arrId=data.instance.get_node(data.selected[0]).id.split('_');
                var fileType=arrId[arrId.length-1];

                if(fileType==='folder'){
                    var token = '{{\Illuminate\Support\Facades\Session::token()}}';
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
                        },beforeSend: function () {
                            //-- Show loading image while execution of ajax request
                            $("div#divLoading").addClass('show');
                        },
                        success: function (dataAjax) {
                            if (dataAjax['result'] === "success") {

                                if(dataAjax['arrData'].length>0){

                                    //-- Check whether children nodes were already generated for this node
                                   if(data.instance.get_node(data.selected[0]).children.length<=0){

                                       //-- Generate new child nodes
                                       for(var i=0;i<dataAjax['arrData'].length;i++){
                                           createNode("#"+data.instance.get_node(data.selected[0]).id, dataAjax['arrData'][i], "last");
                                       }

                                       //-- Open node that was currently clicked
                                       openNode("#"+data.instance.get_node(data.selected[0]).id);
                                   }else{
                                       if(data.instance.get_node(data.selected[0]).state.opened){
                                           //-- Close clicked node
                                           closeNode("#"+data.instance.get_node(data.selected[0]).id);
                                       }else{
                                           //-- Open clicked node
                                          openNode("#"+data.instance.get_node(data.selected[0]).id);
                                       }
                                   }

                                }else{
                                    new Noty({
                                        type: 'success',
                                        layout: 'topRight',
                                        text: '{{trans('messages.folder_empty')}}'
                                    }).show();
                                }
                                
                            }else{
                                new Noty({
                                    type: 'error',
                                    layout: 'bottomLeft',
                                    text: dataAjax['error']
                                }).show();
                            }
                            //-- Hide loading image
                            $("div#divLoading").removeClass('show');
                        }
                    });
                }else{
                    new Noty({
                        type: 'error',
                        layout: 'bottomLeft',
                        text: '{{trans('messages.simple_file')}}'
                    }).show();
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
            $('#jstree').jstree('create_node', $(parent_node), { "text":data['text'], "id":data['id'],"data":data['data'],"icon":data['icon'] }, position, false, false);
        }

        //-- Functionality when open specific node
        function openNode(parent_node) {
            $('#jstree').jstree('open_node', $(parent_node));
        }

        //-- Functionality when close all nodes
        function closeAll() {
            $('#jstree').jstree('close_all');
        }

        //-- Functionality when close specific node
        function closeNode(parent_node) {
            $('#jstree').jstree('close_node', $(parent_node));
        }


    </script>
@stop
