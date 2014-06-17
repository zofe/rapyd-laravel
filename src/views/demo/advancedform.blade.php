@extends('rapyd::demo.demo')

@section('title','DataForm')

@section('body')

    <h1>Advanced DataForm</h1>
    <p>
        Samples of autocomplete feature (in development).<br />
        Everyone is using <a href="http://twitter.github.io/typeahead.js/" target="_blank">twitter typehaead</a> and
        <a href="http://twitter.github.io/typeahead.js/examples/#bloodhound" target="_blank">Bloodhound (Suggestion Engine)</a>.
        <bt />
        
        <ul>
         <li>The most simple is the first one, it just build a local json array using <strong>options()</strong></li>
         <li>The second one is the most smart, use relation.fieldname as fieldname and <strong>remote(search fields, foreign key)</strong><br />
             rapyd will manage an ajax request instancing related entity, searching on search fields, and storing foreign key on select
         </li>
         <li>The last one is the most complete, it's basically like the second one but it add the ability to customize the way you need the search  
        </li>

        </ul>
    

    </p>
    <p>

        {{ $form }}
        {{ Documenter::showMethod("Zofe\\Rapyd\\Controllers\\DemoController", array("anyAdvancedform", "getAuthorlist")) }}
        {{ Documenter::showCode("zofe/rapyd/src/views/demo/form.blade.php") }}
    </p>
@stop