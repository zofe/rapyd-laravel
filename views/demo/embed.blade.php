@extends('rapyd::demo.demo')

@section('title','DataEmbed')

@section('body')

    

    
    <h1>DataEmbed</h1>

    <br />

    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                
                {!! $embed1 !!}
            </div>

            <div class="col-sm-6">

                {!! $embed2 !!}
            </div>
        </div>
    </div>
    
    <p>
        {!! Documenter::showMethod("Zofe\\Rapyd\\Demo\\DemoController", "getEmbed") !!}
        {!! Documenter::showCode("zofe/rapyd/views/demo/embed.blade.php") !!}
    </p>
@stop
