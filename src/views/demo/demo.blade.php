@extends('rapyd::demo.master')

@section('title','Demo')

@section('body')
    <h1>Demo Index</h1>

    @if(Session::has('message'))
    <div class="alert alert-success">
        {{ Session::get('message') }}
    </div>
    @endif

    <p>
        Welcome to Rapyd Demo.<br />
        first click on Populate Database button, then click on menu<br />
        <br />

        {{ link_to('rapyd-demo/schema', "Populate Database", array("class"=>"btn btn-default")) }}
    </p>


@stop


@section('content')

    @include('rapyd::demo.menu')

    @yield('body')

@stop

