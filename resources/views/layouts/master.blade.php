<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(isset($title))
        <title>{{$title}}</title>
    @else
        <title>FTP Browser</title>
    @endif


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css"
          integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="{{custom_asset('lib/noty.js')}}" type="text/javascript"></script>

    <link href="{{custom_asset('css/app_custom.css')}}" rel="stylesheet">

    @yield('styles')

    @yield('scripts_header')
    <style>
        .image_gallery img {
            width: 100%;
        }

        #divLoading {
            display: none;
        }

        #divLoading.show {
            display: block;
            position: fixed;
            z-index: 100;
            background-image: url({{ custom_asset('images/includes/load.gif') }});
            background-color: #666;
            opacity: 0.4;
            background-repeat: no-repeat;
            background-position: center;
            left: 0;
            bottom: 0;
            right: 0;
            top: 0;
        }

    </style>
</head>
<body>
<div>
    <main class="py-4">
        @yield('content')
    </main>
</div>

<div id="divLoading"></div>

<script src="{{custom_asset('js/app.js')}}"></script>
<script src="{{custom_asset('js/app_custom.js')}}"></script>
@yield('scripts')
</body>
</html>
