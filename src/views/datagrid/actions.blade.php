@section('actions')
    <a class="" title="@lang('rapyd::rapyd.show')" href="{{ $uri }}?show={{ $id }}"><span class="glyphicon glyphicon-eye-open"> </span></a>
    <a class="" title="@lang('rapyd::rapyd.modify')" href="{{ $uri }}?modify={{ $id }}"><span class="glyphicon glyphicon-edit"> </span></a>
    <a class="text-danger" title="@lang('rapyd::rapyd.delete')" href="{{ $uri }}?delete={{ $id }}"><span class="glyphicon glyphicon-trash"> </span></a>
@show