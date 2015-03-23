

<ul class="pagination">
@if ($first_page)
    <li><a href="{{ link_route('page',array(1)) }}">&lsaquo;&nbsp;first</a></li>
@endif
@if ($previous_page)
    <li><a href="{{ link_route('page', array($previous_page)) }}">&lt;</a></li>
@endif


@for ($i = $nav_start; $i <= $nav_end; $i++)
    @if  ($i == $current_page) 
        <li class="active"><a href="#">{{ $i }}</a></li>
   @else
        <li><a href="{{ link_route('page', $i) }}">{{ $i }}</a></li>
   @endif
@endfor
@if ($next_page)
    <li><a href="{{ link_route('page', $i) }}">&gt;</a></li>
@endif
@if ($last_page)
    <li><a href="{{ link_route('page', $last_page) }}"> &nbsp;last &rsaquo;</a></li>
@endif
</ul>