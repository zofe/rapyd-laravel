
@include('rapyd::toolbar', array('label'=>$label, 'buttons'=>$buttons['TR']))

<table class="table table-striped">
 
    <tr>
     @foreach ($dg->columns as $column)
            <th>
            @if ($column->orderby)
                @if ($column->name != Input::get('ord'))
                 <a href="{{ $dg->orderbyLink($column->name,'asc') }}">
                     <span class="glyphicon glyphicon-arrow-up"></span>
                 </a>
                @else
                    <span class="glyphicon glyphicon-arrow-up"></span>
                @endif
                @if ('-'.$column->name != Input::get('ord'))
                 <a href="{{ $dg->orderbyLink($column->name,'desc') }}">
                     <span class="glyphicon glyphicon-arrow-down"></span>
                 </a>
                @else
                    <span class="glyphicon glyphicon-arrow-down"></span>
                @endif
                {{ $column->label }}
            @else
                {{ $column->label }}
            @endif
            </th> 
     @endforeach
    </tr>
     
    @foreach ($dg->rows as $row)
        <tr>
            @foreach ($row as $cell)
            <td>{{ $cell }}</td>
            @endforeach
        </tr>
    @endforeach
</table>


@if ($dg->havePagination())
    <div class="pagination">
    {{ $dg->links() }}
    </div>
@endif
