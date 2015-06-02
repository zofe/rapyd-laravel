@extends('rapyd::demo.master')

@section('title','Demo')

<?php $is_rapyd = (Request::server('HTTP_HOST') == "www.rapyd.com") ? true : false; ?>
@section('body')

    @if (isset($is_rapyd) AND $is_rapyd)
        <!-- Google Tag Manager -->
        <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-5VL356"
                          height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                    '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-5VL356');</script>
        <!-- End Google Tag Manager -->
    @endif

    
    <h1>Demo Index</h1>

    @if(Session::has('message'))
    <div class="alert alert-success">
        {!! Session::get('message') !!}
    </div>
    @endif

    <p>
        Welcome to Rapyd Demo.<br />

        @if (isset($is_rapyd) AND $is_rapyd)

        @else
            first click on Populate Database button, then click on menu<br />
            <br />
            {!! link_to('rapyd-demo/schema', "Populate Database", array("class"=>"btn btn-default")) !!}
        @endif

        <br />
        <br />
        Click on tabs to see how rapyd widgets can save your time.<br />
        The first tab <strong>Models</strong> is included just to show  models and relations used in this demo,
        there isn't custom code, rapyd can work with your standard or extended Eloquent models.
        <strong>DataSet</strong>, <strong>DataGrid</strong>, <strong>DataFilter</strong>,
        <strong>DataForm</strong>, and <strong>DataEdit</strong> are the "widgets" that rapyd provide.

    </p>


@stop


@section('content')

    @include('rapyd::demo.menu')

    @yield('body')

    @include('rapyd::demo.disqus')

    @if (isset($is_rapyd) AND $is_rapyd)
        <div class="privacy-overlay">
            <div class="privacy-modal"></div>
        </div>
        <link href="/css/policy.css" rel="stylesheet">
        <script src="/js/policy.js"></script>
    @endif
@stop
