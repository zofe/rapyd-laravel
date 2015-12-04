@extends('rapyd::demo.demo')

@section('title','DataTree')

@section('body')

    <h1>DataTree</h1>
        {!! $tree !!}
    <p>

        {!! Documenter::showMethod("Zofe\\Rapyd\\Demo\\DemoController", "getGrid") !!}
        {!! Documenter::showCode("zofe/rapyd/views/demo/grid.blade.php") !!}
    </p>
@stop
