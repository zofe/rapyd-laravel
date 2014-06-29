<div class="form-group{{$field->has_error}}">

    <label for="{{ $field->name }}" class="sr-only">{{ $field->label.$field->star }}</label>
    <span id="div_{{ $field->name }}">

        {{ $field->output }}

        
        @if(count($field->messages))
        <span class="glyphicon glyphicon-remove form-control-feedback"></span>
        @endif

    </span>

</div>

