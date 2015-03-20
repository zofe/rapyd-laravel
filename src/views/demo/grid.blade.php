@extends('rapyd::demo.demo')

@section('title','DataGrid')

@section('body')

    <h1>DataGrid</h1>
    <p>

        {{ $grid }}
        {{ Documenter::showMethod("Zofe\\Rapyd\\Controllers\\DemoController", "getGrid") }}
        {{ Documenter::showCode("zofe/rapyd/src/views/demo/grid.blade.php") }}
    </p>
@stop
