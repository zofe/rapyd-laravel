@if (in_array($field->type, array('hidden','auto')) OR !$field->has_wrapper )

    {!! $field->output !!}

    @if ($field->message!='')
    <span class="help-block">
        <span class="glyphicon glyphicon-warning-sign"></span>
        {!! $field->message !!}
    </span>
    @endif

@else
    <div class="form-group{!!$field->has_error!!}" id="fg_{!! $field->name !!}">

        @if ($field->has_label)
            <label for="{!! $field->name !!}" class="col-sm-2 control-label{!! $field->req !!}">{!! $field->label !!}</label>
            <div class="col-sm-10" id="div_{!! $field->name !!}">
        @else
            <div class="col-sm-12" id="div_{!! $field->name !!}">
        @endif

            {!! $field->output !!}

            @if(count($field->messages))
                @foreach ($field->messages as $message)
                    <span class="help-block">
                        <span class="glyphicon glyphicon-warning-sign"></span>
                        {!! $message !!}
                    </span>
                @endforeach
            @endif

            </div>
    </div>
    @foreach($field->children as $child)
        <div class="conditional" data-cond-option="{!! $field->name . $field->arrayFragment() !!}" data-cond-value="{!! $child['value'] !!}" data-cond-operator="{!! $child['operator'] !!}">
            @include('rapyd::dataform.field', ['field' => $child['child']])
        </div>
    @endforeach
@endif
