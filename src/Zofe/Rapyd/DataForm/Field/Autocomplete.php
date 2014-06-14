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


    
    
    

    public function options($options, $remote = "")
    {
        if (is_array($options)) {
            $this->options += $options;
        }
        return $this;
    }

   /* public function option($value = '', $description = '')
    {
        $this->options[$value] = $description;
        return $this;
    }*/
    
    

    function getValue()
    {
        parent::getValue();

        //dd($this->value);
        
        /*if (Input::get($this->name))
        {
            if ($this->record_label, $this->hidden_field_id))
            {
                if (isset($this->model) AND is_object($this->model) AND $this->model->loaded)
                {
                    //to-do
                    $this->ajax_rsource = parent::replace_pattern($this->ajax_rsource,$this->model->get_all());
                    $this->value = file_get_contents($this->ajax_rsource);
                }

            }
        }*/

    }

    public function remote($record_label = null, $record_id = null, $remote = null)
    {
        $this->record_label = ($record_label!="") ? $record_label : $this->db_name ;
        $this->record_id = ($record_id!="") ? $record_id : $this->db_name ;
        if ($remote!="") {
            $this->remote = $remote;
        } else {

            $data =  array('entity'=>'Article', 'field'=>'firstnama');
            $hash = substr(md5(serialize($data)), 0, 12);
            Session::put($hash, $data);

            route('rapyd.remote', array('hash'=> $hash));
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

                $output  =  Form::text("auto_".$this->db_name, $this->value, $this->attributes)."\n";
                $output .=  Form::hidden($this->db_name, $this->value);

                //$mustmatch = ($this->must_match) ? 'true' : 'false';
                //$autofill = ($this->auto_fill) ? 'true' : 'false';

                $script = <<<acp

                var blod_{$this->db_name} = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('auto_{$this->db_name}'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    remote: '{$this->remote}?q=%QUERY'
                });
                blod_{$this->db_name}.initialize();
            
                $('#div_{$this->db_name} .typeahead').typeahead(null, {
                    name: 'esami',
                    displayKey: '{$this->record_label}',
                    highlight: true,
                    minLength: {$this->min_chars},
                    source: blod_{$this->db_name}.ttAdapter(),
                    templates: {
                        suggestion: Handlebars.compile('{{{$this->record_label}}}')
                    }
                }).on("typeahead:selected typeahead:autocompleted", 
                    function(e,data) { $('{$this->db_name}').val() = data.{$this->record_id};
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
