@extends('rapyd::demo.demo')

@section('title','DataFilter')

@section('body')

    <h1>DataFilter</h1>

    <p>
        {{ $filter }}
        {{ $grid }}
        {{ Documenter::showMethod("Zofe\\Rapyd\\Controllers\\DemoController", "getFilter") }}
        {{ Documenter::showCode("zofe/rapyd/src/views/demo/filtergrid.blade.php") }}
    </p>

@stop