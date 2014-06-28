<div class="form-group{{$field->has_error}}">

    <label for="{{ $field->name }}" class="col-sm-2 control-label">{{ $field->label.$field->star }}</label>
    <div class="col-sm-10" id="div_{{ $field->name }}">

        {{ $field->output }}

        @if(count($field->messages))
            @foreach ($field->messages as $message)
                <span class="help-block">
                    <span class="glyphicon glyphicon-warning-sign"></span> 
                    {{ $message }}
                </span>
            @endforeach
        @endif

    </div>

</div>

