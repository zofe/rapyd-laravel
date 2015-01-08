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

        <div class="container">

        {{ $form->header }}

            {{ $form->message }}

            @if(!$form->message)


                <div class="row">

                    <div class="col-sm-4">
                        {{ Form::field('title')->all() }}

                        {{ $form->render('categories.name') }}

                        {{ $form->render('photo') }}

                    </div>

                    <div class="col-sm-8">

                         Body: {{ $form->field('body') }}<br />
                    </div>

                </div>
            @endif


        {{ $form->footer }}

       </div>

        {{ Documenter::showMethod("Zofe\\Rapyd\\Controllers\\TestController", array("anyStyledform")) }}
        {{ Documenter::showCode("zofe/rapyd/src/views/demo/test.blade.php") }}

@stop
