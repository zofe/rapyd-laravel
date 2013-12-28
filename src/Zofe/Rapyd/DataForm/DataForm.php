<?php namespace Zofe\Rapyd\DataForm;

use Zofe\Rapyd\Widget as Widget;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\View;

class DataForm extends Widget
{

    public $model;
    public $output = "";
    protected $source;
    public $fields = array();
    public $hash = "";
    protected $process_url = '';
    
    public function add($name, $label, $type, $validation = '')
    {

        if (strpos($type, "\\")) {
            $field_class = $type;
        } else {
            $field_class = "\Zofe\Rapyd\DataForm\Field"."\\".ucfirst($type);
        }
        $field_obj = new $field_class($name, $label);
        if ($field_obj->type == "file") {
            $this->multipart = true;
        }
        //share model
        if (isset($this->model)) {
            $field_obj->model = $this->model;
        }
        //default group
        if (isset($this->default_group) && !isset($field_obj->group)) {
            $field_obj->group = $this->default_group;
        }
        $this->fields[$name] = $field_obj;
        return $field_obj;
    }
    
    public function submit($label)
    {
        $field_obj = $this->add('submit',$label, "submit");
        return $field_obj;
    }

    public function &field($field_name)
    {
        if (isset($this->fields[$field_name])) {
            return $this->fields[$field_name];
        }
    }

    public static function create()
    {
       $ins = new static;
       $ins->cid = $ins->getIdentifier();
       return $ins;
    }

    public static function source($source='')
    {
        $ins = new static;
        if (is_object($source) && is_a($source, "\Illuminate\Database\Eloquent\Model")) {
            $ins->model = $source;
        }
       $ins->cid = $ins->getIdentifier();
       return $ins;
    }
    
    
    
    
    public function build_fields()
    {
        foreach ($this->fields as $field) {
            //share status
            $field->status = $this->status;
            $field->build();
        }
    }

    public function build($view = '')
    {

        //detect form status (output)
        if (isset($this->model)) {
            $this->status = ($this->model->loaded) ? "modify" : "create";
        } else {
            $this->status = "create";
        }
        //build fields
        $this->build_fields();
        //process only if instance is a dataform
        if (is_a($this, 'dataform_library')) {
            //build buttons
            $this->build_buttons();
            //sniff action
            if (isset($_POST) && ( $this->url->value('process'))) {
                $this->action = ($this->status == "modify") ? "update" : "insert";
            }
            //process
            $this->process();
        }


        $data = get_object_vars($this);
        //$data['container'] = $this->button_containers();
        $form_type = 'open';
        // See if we need a multipart form
        foreach ($this->fields as $field_obj) {
            if ($field_obj instanceof upload_field) {
                $form_type = 'open_multipart';
                break;
            }
        }
        // Set the form open and close
        if ($this->status == 'show') {
            $data['form_begin'] = '<div class="form">';
            $data['form_end'] = '</div>';
        } else {
            $data['form_begin'] =  Form::open(array('class' => "form-horizontal",'role'=>"form"));
            //($this->process_url, $this->attributes);
            $data['form_end'] =   Form::close();
        }


        $data['groups'] = $this->regroupFields($this->orderFields($this->fields));
        //$data['extra_class'] = $this->extra_class;
        if ($view == '')
            $view = 'rapyd::dataform';
        return View::make($view, $data);
    }

    public function getForm($view = '')
    {
        $this->output = $this->build($view);
        return $this->output;
    }

    protected function orderFields($fields)
    {
        //prepare nested fields
        foreach ($fields as $field_name => $field_ref) {
            if (isset($field_ref->in)) {
                if (isset($series_of_fields[$field_ref->in][0]) && $field_ref->label != "")
                    $series_of_fields[$field_ref->in][0]->label .= '/' . $field_ref->label;
                $series_of_fields[$field_ref->in][] = $field_ref;
            }
            else {
                $series_of_fields[$field_name][] = $field_ref;
            }
        }

        //prepare grouped fields
        $ordered_fields = array();
        foreach ($fields as $field_name => $field_ref) {
            if (!isset($field_ref->in)) {
                if (isset($field_ref->group)) {
                    $ordered_fields[$field_ref->group][$field_name] = $series_of_fields[$field_name];
                } else {
                    $ordered_fields["ungrouped"][$field_name] = $series_of_fields[$field_name];
                }
            }
        }
        return $ordered_fields;
    }

    protected function regroupFields($ordered_fields)
    {
        //build main array
        $groups = array();
        foreach ($ordered_fields as $group => $series_of_fields) {
            unset($gr);

            $gr["group_name"] = $group;

            foreach ($series_of_fields as $series_name => $serie_fields) {
                unset($sr);
                $sr["is_hidden"] = false;
                $sr["series_name"] = $series_name;

                foreach ($serie_fields as $field_ref) {
                    unset($fld);
                    if (($field_ref->status == "hidden" || $field_ref->visible === false || in_array($field_ref->type, array("hidden", "auto")))) {
                        $sr["is_hidden"] = true;
                    }

                    $fld["label"] = $field_ref->label;
                    $fld["id"] = $field_ref->name;
                    $fld["field"] = $field_ref->output;
                    $fld["type"] = $field_ref->type;
                    $fld["star"] = $field_ref->star;
                    $sr["fields"][] = $fld;
                }
                $gr["series"][] = $sr;
            }
            $groups[] = $gr;
        }

        return $groups;
    }

}
