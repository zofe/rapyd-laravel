@extends('rapyd::demo.master')

@section('title','Models')

@section('body')
    <h1>Models Used</h1>


    <p>
        These are the entities used in this demo:
        <br />

    </p>

    {{ Documenter::showCode("zofe/rapyd/src/models/Article.php") }}
    {{ Documenter::showCode("zofe/rapyd/src/models/ArticleDetail.php") }}
    {{ Documenter::showCode("zofe/rapyd/src/models/Author.php") }}
    {{ Documenter::showCode("zofe/rapyd/src/models/Category.php") }}
    {{ Documenter::showCode("zofe/rapyd/src/models/Comment.php") }}

@stop


@section('content')

    @include('rapyd::demo.menu')

    @yield('body')

@stop
