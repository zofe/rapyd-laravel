
@include('rapyd::toolbar', array('label'=>$label, 'buttons_right'=>$buttons['TR']))

<table class="table table-striped">
    <thead>
    <tr>
     @foreach ($dg->columns as $column)
            <th>
            @if ($column->orderby)
                @if ($dg->onOrderby($column->name, 'asc'))
                    <span class="glyphicon glyphicon-arrow-up"></span>
                @else
                    <a href="{{ $dg->orderbyLink($column->name,'asc') }}">
                        <span class="glyphicon glyphicon-arrow-up"></span>
                    </a>
                @endif
                @if ($dg->onOrderby($column->name, 'desc'))
                    <span class="glyphicon glyphicon-arrow-down"></span>
                @else
                    <a href="{{ $dg->orderbyLink($column->name,'desc') }}">
                        <span class="glyphicon glyphicon-arrow-down"></span>
                    </a>
                @endif
                {{ $column->label }}
            @else
                {{ $column->label }}
            @endif
            </th> 
     @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($dg->rows as $row)
        <tr>
            @foreach ($row as $cell)
            <td>{{ $cell }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody> 
</table>


@if ($dg->havePagination())
    <div class="pagination">
    {{ $dg->links() }}
    </div>
@endif
