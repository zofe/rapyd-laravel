@extends('rapyd::demo.demo')

@section('title','DataForm')

@section('body')

    @include('rapyd::demo.menu_form')

    <h1>DataForm (with custom output)</h1>
  
    <p>
        There is not only @{{ $form }} to show your form.<br />
        If you need to customize something, wrap fields, grouping elements etc..<br />
        you can use partial rendering. See below
        
        
        
    </p>  

        
        {{ $form->header }}

            {{ $form->message }}
    
            <br />
    
            @if(!$form->message)
            
                {{ $form->field('title')->label }}
                {{ $form->field('title')->output }}
                {{ implode(', ', $form->field('title')->messages) }}
                <br /> 
                {{ $form->field('body')->output }}
                <br />
                {{ $form->field('categories.name')->label }}
                {{ $form->field('categories.name')->output }}
            @endif
            <br />
        
        {{ $form->footer }}

        <br />
        
        {{ Documenter::showMethod("Zofe\\Rapyd\\Controllers\\DemoController", array("anyStyledform")) }}
        {{ Documenter::showCode("zofe/rapyd/src/views/demo/styledform.blade.php") }}

@stop