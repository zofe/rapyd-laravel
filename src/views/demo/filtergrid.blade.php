@extends('rapyd::demo.demo')

@section('title','DataFilter')

@section('body')

    @include('rapyd::demo.menu_filter')

    <h1>DataFilter</h1>

    <p>
        {{ $filter }}
        {{ $grid }}
        {{ Documenter::showMethod("Zofe\\Rapyd\\Controllers\\DemoController", "getFilter") }}
        {{ Documenter::showCode("zofe/rapyd/src/views/demo/filtergrid.blade.php") }}
    </p>

@stop
