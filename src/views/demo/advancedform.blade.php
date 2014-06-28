@extends('rapyd::demo.demo')

@section('title','DataForm')

@section('body')

    @include('rapyd::demo.menu_form')

    <h1>DataForm (advanced stuffs)</h1>
    <p>
        Samples of autocomplete feature (in development).<br />
        Everyone is using <a href="http://twitter.github.io/typeahead.js/" target="_blank">twitter typehaead</a> and
        <a href="http://twitter.github.io/typeahead.js/examples/#bloodhound" target="_blank">Bloodhound (Suggestion Engine)</a>.<br />
        The last one is using also <a href="https://github.com/TimSchlechter/bootstrap-tagsinput" target="_blank">TagsInput</a>.
        <br />
        
        <ul>
         <li>The most simple is the first one, it just build a local json array using <strong>options()</strong></li>
         <li>The second one is the most smart, use relation.fieldname as fieldname and <strong>search(array of search fields)</strong><br />
             rapyd will manage an ajax request instancing related entity, searching on search fields, and storing foreign key on select
         </li>
         <li>The third is the most complete, it's basically like the second one but it add the ability to customize the search query </li>
         <li>The last is a sample of "tags" field to manage a belongsToMany, it support also search() remote()</li>

        </ul>

        <br />
        Options are only "Jane" and "Jhon" but it's just to test if it works.<br />
        Categories are "Category 1" .. "Category 5"
    </p>
    <p>

        {{ $form }}
        {{ Documenter::showMethod("Zofe\\Rapyd\\Controllers\\DemoController", array("anyAdvancedform", "getAuthorlist")) }}
    </p>
@stop