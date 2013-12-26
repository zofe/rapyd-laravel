

<table class="table table-striped">
 
    <tr>
     @foreach ($dg->columns as column)
            <th>
            @if $column->orderby
                 <a href="{{ $dg->orderby_link($column->orderby_field,'asc') }}">
                     <i class="fa fa-angle-up"></i>
                 </a>
                 <a href="{{ $dg->orderby_link($column->orderby_field,'desc') }}">
                     <i class="fa fa-angle-down"></i>
                 </a> 
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
            <td>{{ cell->value }}</td>
            @endforeach
        </tr>
    @endforeach
</table>


<div class="pagination">
{{ $dg->links() }}
</div>

