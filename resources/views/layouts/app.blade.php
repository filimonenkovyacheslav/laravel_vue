<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <!--<script async src="https://www.googletagmanager.com/gtag/js?id=UA-129593277-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-129593277-1');
    </script>-->
    <!--
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: "ca-pub-3316346585884811",
            enable_page_level_ads: true
        });
    </script>
    -->

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="index, nofollow" />

    @yield('meta_og')

    <!-- Title -->
    <title>@yield('title', __('frontend.main_title'))
    </title>

    <!-- Styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!--<link rel="stylesheet" href="{{ asset('css/effects.css') }}">-->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flags.min.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin=""/>
    @yield('css')

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
    <script src="https://www.google.com/recaptcha/api.js?render=explicit"></script>
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ url('/js/jsvars.js') }}"></script>
    <script src="{{ url('/js/lang.js') }}"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!--<script data-ad-client="ca-pub-3316346585884811" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>-->
    @yield('js_head')

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:light,bold,italic" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:light,bold,italic" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=PT+Sans:light,bold,italic" rel="stylesheet">
	@yield('fonts')
</head>
<body class="{{ app('request')->input('es') ? 'es' : '' }}@yield('body_class', '')">
    <div id="app" class="{{ $is_admin === true ? 'profile' : 'content' }}">
        @yield('content')
        @yield('footer')
    </div>

    @yield('js_foot')
</html>
