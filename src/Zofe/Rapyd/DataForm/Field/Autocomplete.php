<?php

namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Zofe\Rapyd\Rapyd;

class Autocomplete extends Field {

    public $type = "autocompleter";
    public $css_class = "autocompleter typeahead";

    public $remote;

    public $record_id;
    public $record_label;
    public $hidden_field_id;

    public $must_match = false;
    public $auto_fill = false;
    public $parent_id = '';
    
    public $min_chars = '2';



    public function remote($record_label = null, $record_id = null, $remote = null)
    {
        $this->record_label = ($record_label!="") ? $record_label : $this->db_name ;
        $this->record_id = ($record_id!="") ? $record_id : $this->db_name ;
        if ($remote!="") {
            $this->remote = $remote;
            if (is_array($record_label))
            {
                $this->record_label = current($record_label);
            }
        } else {

            $data["entity"] = get_class($this->relation->getRelated());
            $data["field"]  = $record_label;
            if (is_array($record_label))
            {
                $this->record_label = $this->rel_field;
            }
            $hash = substr(md5(serialize($data)), 0, 12);
            Session::put($hash, $data);

            $this->remote = route('rapyd.remote', array('hash'=> $hash));
        }
        
    }


    public function build()
    {
        $output = "";
        Rapyd::css('packages/zofe/rapyd/assets/autocomplete/autocomplete.css');
        Rapyd::js('packages/zofe/rapyd/assets/autocomplete/typeahead.bundle.min.js');
        Rapyd::js('packages/zofe/rapyd/assets/template/handlebars.js');

        unset($this->attributes['type']);

        
        if (parent::build() === false) return;


        switch ($this->status)
        {
            case "disabled":
            case "show":
                if ( (!isset($this->value)) )
                {
                    $output = $this->layout['null_label'];
                } elseif ($this->value == ""){
                    $output = "";
                } else {
                    $output = nl2br(htmlspecialchars($this->value));
                    if ($this->is_multiple)
                        $output = '<div class="textarea_disabled">'.$output.'</div>';
                }
                break;

            case "create":
            case "modify":
                
                if (Input::get("auto_".$this->db_name))
                {
                    $autocomplete = Input::get("auto_".$this->db_name);
                } elseif ($this->relation != null)
                {
                    $name = $this->rel_field;
                    $autocomplete = @$this->relation->get()->first()->$name;
                } else {
                    
                    $autocomplete = $this->value;
                }


                $output  =  Form::text("auto_".$this->name, $autocomplete, array_merge($this->attributes, array('id'=>"auto_".$this->name)))."\n";
                $output .=  Form::hidden($this->name, $this->value, array('id'=>$this->name));

                //$mustmatch = ($this->must_match) ? 'true' : 'false';
                //$autofill = ($this->auto_fill) ? 'true' : 'false';

                $script = <<<acp

                var blod_{$this->name} = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('auto_{$this->name}'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: '{$this->remote}?q=%QUERY'
                });
                blod_{$this->name}.initialize();
            
                $('#div_{$this->name} .typeahead').typeahead(null, {
                    name: '{$this->name}',
                    displayKey: '{$this->record_label}',
                    highlight: true,
                    minLength: {$this->min_chars},
                    source: blod_{$this->name}.ttAdapter(),
                    templates: {
                        suggestion: Handlebars.compile('{{{$this->record_label}}}')
                    }
                }).on("typeahead:selected typeahead:autocompleted", 
                    function(e,data) { $('#{$this->name}').val(data.{$this->record_id});
                });
acp;

                $output .= Rapyd::script($script);


                break;

            case "hidden":
                $output = Form::hidden($this->db_name, $this->value);
                break;

            default:;
        }
        $this->output = "\n".$output."\n". $this->extra_output."\n";
    }

}
