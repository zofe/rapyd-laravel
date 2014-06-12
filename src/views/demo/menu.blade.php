
    <ul class="nav nav-pills">
        <li @if (Request::is('rapyd-demo')) class="active"@endif>{{ link_to("rapyd-demo", "Index") }}</li>
        <li @if (Request::is('rapyd-demo/models')) class="active"@endif>{{ link_to("rapyd-demo/models", "Models") }}</li>
        <li @if (Request::is('rapyd-demo/grid*')) class="active"@endif>{{ link_to("rapyd-demo/grid", "DataGrid") }}</li>
        <li @if (Request::is('rapyd-demo/filter*')) class="active"@endif>{{ link_to("rapyd-demo/filter", "DataFilter") }}</li>
        <li @if (Request::is('rapyd-demo/form*')) class="active"@endif>{{ link_to("rapyd-demo/form", "DataForm") }}</li>
        <li @if (Request::is('rapyd-demo/edit*')) class="active"@endif>{{ link_to("rapyd-demo/edit", "DataEdit") }}</li>
    </ul>



