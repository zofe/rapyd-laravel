


{{ $form_begin }}

@if ($label!="")

{{ $label }}

@endif


@if (isset($groups))
    @foreach ($groups as $group)

        @if ($group["group_name"] != "ungrouped")
        <fieldset id="group_{{ strtolower(preg_replace('/[^A-Za-z0-9_]*/', '', $group["group_name"])) }}">
            <legend>{{ $group["group_name"] }}</legend>
        @endif
            
        @foreach ($group["series"] as $field_series)
            
            @if ($field_series["is_hidden"])
                @foreach ($field_series["fields"] as $field)
                  {{ $field["field"] }}
                @endforeach
            @else
            
            
                <div class="form-group">
                @if (isset($field_series["fields"]))
                
                        {{-- */ $first_field=true; /* --}}
                        @foreach ($field_series["fields"] as $field)
                            @if($first_field)
                                    {{-- */ $first_field=false; /* --}}
                                    @if (($field["type"] == "container") || ($field["type"] == "iframe"))
                                        <a name="anchor_{{ $field["id"] }}"></a>
                                        <div class="col-sm-10" id="{{ $field["id"] }}">
                                        {{ $field["field"] }}
                                    @elseif ($field["type"] == "submit")
                                        
                                        <div class="col-sm-10 col-sm-offset-2">
                                            {{ $field["field"] }}
                                    @else
                                        <label for="{{ $field["id"] }}" class="col-sm-2 control-label">{{ $field["label"].$field["star"] }}</label>
                                        <div class="col-sm-10" id="div_{{ preg_replace('/[^A-Za-z0-9_]*/', '', $field["id"]) }}">
                                        {{ $field["field"] }}
                                    @endif
                            @else
                                   {{ $field["field"] }}
                            @endif
                        @endforeach
                        
                @endif
                     </div>
                </div>
            @endif

        @endforeach

        
        @if ($group["group_name"] != "ungrouped")
            </fieldset>
        @endif

    @endforeach
@endif



{{ $form_end }}

