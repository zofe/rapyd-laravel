@extends('rapyd::demo.demo')

@section('title','DataTree')

@section('body')

    <h1>DataTree</h1>
        {!! $tree !!}
    <p>

        {!! Documenter::showMethod("Zofe\\Rapyd\\Demo\\DemoController", "anyDatatree") !!}
        {!! Documenter::showMethod("Zofe\\Rapyd\\Demo\\DemoController", "anyMenuedit") !!}
        {!! Documenter::showCode("zofe/rapyd/views/demo/tree.blade.php") !!}
    </p>
@stop
