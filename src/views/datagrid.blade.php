

<table class="table table-striped">
 
    <tr>
     @foreach ($dg->columns as $column)
            <th>
            @if ($column->orderby)
                 <a href="{{ $dg->orderbyLink($column->name,'asc') }}"><span class="glyphicon glyphicon-arrow-up"></span></a>
                 <a href="{{ $dg->orderbyLink($column->name,'desc') }}"><span class="glyphicon glyphicon-arrow-down"></span></a> 
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


<div class="pagination">
{{ $dg->links() }}
</div>

