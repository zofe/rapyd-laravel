@extends('rapyd::demo.demo')

@section('title','DataEdit')

@section('body')

    <h1>DataEdit</h1>
    <p>

        {{ $edit }}
        {{ Documenter::showMethod("Zofe\\Rapyd\\Controllers\\DemoController", "anyEdit") }}
        {{ Documenter::showCode("zofe/rapyd/src/views/demo/edit.blade.php") }}
    </p>
@stop
