
@section('df.header')
    {{ $df->form_begin }}
    @include('rapyd::toolbar', array('label'=>$df->label, 'buttons_right'=>$df->buttons['TR']))
@show

@section('df.message')

    @if ($df->message != '')
    <div class="alert alert-success">{{ $df->message}}</div>
    @endif

@show

@section('df.fields')
    
    @if ($df->message == '')
        @foreach ($df->fields as $fieldname => $field)

            @include('rapyd::dataform.field')
    
        @endforeach
    @endif
    
@show

@section('df.buttons')

    @if ( ! empty($buttons->actions))
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            @foreach ($buttons->actions as $button)
                {{ $button }}
            @endforeach
        </div>
    </div>
    @endif

@show

@section('df.footer')
    @include('rapyd::toolbar', array('buttons_left'=>$df->buttons['BL'], 'buttons_right'=>$df->buttons['BR'] ))
    {{ $df->form_end }}
@show
