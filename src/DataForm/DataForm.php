<?php namespace Zofe\Rapyd\DataForm;

use Zofe\Burp\BurpEvent;
use Zofe\Rapyd\DataForm\Fields\Field;

class DataForm
{
    public $model;
    
    
    public $fields;
    public $values = array();
    
    public $multipart = false;
    public $output = '';
    public $validator;

    public $process_status = "idle";
    public $status = "edit";
    public $action = "idle";

    public $open;
    public $close;
    public $label;
    public $button_container = array( "TR"=>array(), "BL"=>array(), "BR"=>array() );
    public $message = "";
    public $message_class = "alert alert-success"; 
    public $rules = "";
    public $error = "";
    protected $method = 'POST';
    protected $redirect = null;
    protected $process_url = '';
    protected $orientation = 'horizontal';
    protected $form_callable = false;
    protected $attributes = array('class' => "form-horizontal", 'role' => "form", 'method' => 'POST');
    

    /**
     * Main method, set source (model) or create an empty form
     *
     * @param $source
     * @return static
     */
    public static function create($source = null)
    {
        $ins = new static();
        $ins->process_url = link_route('save');
        $ins->fields = new FieldCollection();
        if (is_object($source) && is_a($source, '\Illuminate\Database\Eloquent\Model')) {
            $ins->model = $source;
            //$ins->status = ($ins->model->exists) ? "modify" : "create";
        }
        BurpEvent::listen('dataform.save', array($ins, 'save'));
        return $ins;
    }

    /**
     * alias for create()
     * 
     * @param string $source
     * @return DataForm
     */
    public static function source($source = '')
    {
        return self::create($source);
    }

    /**
     * save data on model or just fill fields values
     *
     * @return bool
     */
    public function save()
    {
        $this->setFieldValues();
        $valid = $this->isValid();
        
        if ($valid) {
            $this->getFieldValues();
            $valid = $this->saveModel();
        }
        
        if ($valid) {
            
            $this->process_status = "success";
            
            //callable
            if ($this->form_callable) {
   
                $callable = $this->form_callable;
                $result = $callable($this);
                
                //todo: verificare se nella closure c'è un header location un redirect di laravel (e in caso, gestirlo)
                if ($result) {
                    return $result;
                }

            }
            //cleanup submits if success
            if ($this->process_status == 'success') {
                $this->removeFieldType('submit');
                
            }
        }
        
        //altrimenti non è valido o è fallito il salvataggio
        $this->process_status = "error";
    }
    
    /**
     * set field values
     *
     * @return bool
     */
    protected function setFieldValues($from_model = false)
    {
        foreach ($this->fields as $field)
        {
            if ($field->default_value) {
                $field->setValue($field->default_value);
            }
            if ($field->request_refill == true && is_route('save') ) {
                $field->setValue(request_input($field->name, $field->default_value));
                $field->is_refill = true;
                
            } elseif ($from_model && isset($this->model)) {
                
                if ($this->model->offsetExists($field->name)) {

                    $field->setValue($this->model->{$field->name});
                }
            }
        }
    }

    /**
     * get field values 
     *
     * @return bool
     */
    protected function getFieldValues() {
        foreach ($this->fields as $field)
        {
            $this->values[$field->name] = $field->getValue($field->name);
        }
    }

    protected function saveModel() {
        if (isset($this->model)) {

            foreach ($this->values as $name => $value)
            {
                if ($this->model->offsetExists($name)) {
                    
                    $this->model->setAttribute($name, $value);
                }

            }
            if ($this->model->offsetExists('created_at') || $this->model->offsetExists('created_at')) {
                $this->model->touch();
            }
            return $this->model->save();
            
        } else {
            return true;
        }
    }
    
    /**
     * remove field from fields list
     *
     * @param $fieldname
     * @return $this
     */
    public function removeField($fieldname)
    {
        if (isset($this->fields[$fieldname]))
            unset($this->fields[$fieldname]);

        return $this;
    }

    /**
     * remove field where type==$type from field list and button container
     *
     * @param $type
     * @return $this
     */
    public function removeFieldType($type)
    {
        $this->fields->removeType($type);
        foreach ($this->button_container as $container => $buttons) {
            foreach ($buttons as $key=>$button) {
                if (strpos($button, 'type="'.$type.'"')!==false) {
                    $this->button_container[$container][$key] = "";
                }
            }
        }

        return $this;
    }
    
    /**
     * get entire field output (label, output, and messages)
     * 
     * @param $field_name
     * @param  array  $attributes
     * @return string
     */
    public function render($field_name, array $attributes = array())
    {
        $field = $this->fields->get($field_name, $attributes);
        return (is_object($field)) ? $field->all() : null;
    }

    /**
     * get field instance from fields array
     * 
     * @param $field_name
     * @param  array    $attributes
     * @return \Zofe\Rapyd\DataForm\Field $field
     */
    public function field($field_name, array $attributes = array())
    {
        return $this->fields->get($field_name, $attributes);
    }
    
    /**
     * add field to the form using name, label and type
     * 
     * @param $name
     * @param $label
     * @param $type
     * @return mixed
     */
    public function add($name, $label, $type)
    {
        return $this->fields->add($name, $label, $type);
    }
    
    /**
     * add a submit button
     *
     * @param string $name
     * @param string $position
     * @param array  $options
     * @return $this
     */
    public function submit($name, $position = "BL", $options = array())
    {
        $options = array_merge(array("class" => "btn btn-primary"), $options);
        $this->button_container[$position][] = Form::submit($name, $options);

        return $this;
    }
    
    /**
     * Magic method to catch all appends using $form->{fieldtype}(...)
     *
     * @param  string $name
     * @param  Array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (count($arguments) === 2) {
            return $this->add($arguments[0], $arguments[1], $name);
        }
    }

    /**
     * field validation, check all validation rules
     *
     * @return bool
     */
    protected function isValid()
    {
        if ($this->error != "") {
            return false;
        }

        $rules = array();
        $messages = array();
        $attributes = array();
        foreach ($this->fields as $field) {
            //$field->action = $this->action;
            if (isset($field->rule)) {
                $rules[$field->name] = $field->rule;
                $messages[$field->name] = null;
                $attributes[$field->name] = $field->label;
            }
        }
        if (!isset($this->validator)) {
            $this->validator = validator($_POST, $rules, $messages, $attributes);
        }
        if (isset($rules)) {

            return !$this->validator->fails();
        } else {
            return true;
        }
    }

    /**
     * build each field and share some data from dataform to field 
     * (form status, validation errors)
     */
    protected function buildFields()
    {
        $messages = (isset($this->validator)) ? $this->validator->messages() : false;
        foreach ($this->fields as $field) {
            $field->status = $this->status;
            $field->orientation = $this->orientation;
            if ($messages and $messages->has($field->name)) {
                $field->messages = $messages->get($field->name);
                $field->has_error = " has-error";
            }
            $field->build();
        }
    }

    /**
     * prepare some var (form open tag, errors, etc) 
     */
    public function prepareForm()
    {
        // Set the form open and close
        if ($this->status == 'show') {
            $this->open = '<div class="form">';
            $this->close = '</div>';
        } else {

            $this->open = Form::open($this->process_url, $this->attributes);
            $this->close = Form::hidden('save', 1) . Form::close();

            if ($this->method == "GET") {
                $this->close = Form::hidden('search', 1) . Form::close();
            }
        }
        if (isset($this->validator)) {
            $this->errors = $this->validator->messages();
            $this->error .=  implode('<br />',$this->errors->all());
        }
    }
    
    public function build($view = null)
    {
        $this->setFieldValues(true);
        BurpEvent::flush('dataform.save');

        $view = ($view) ? $view : 'dataform.dataform';
        $this->buildFields();
        $this->prepareForm();
        $this->output = blade($view, array('df'=>$this));
        
        //build each section reparately (for custom forms) 
        $sections = blade($view, array('df'=>$this), null, false)->renderSections();
        $this->header = $sections['df.header'];
        $this->footer = $sections['df.footer'];
        $this->body = @$sections['df.fields'];
    }
    
    public function getForm($view = null)
    {
        $this->build($view);
        return $this->output;
    }

    public function __toString()
    {
        if ($this->output == "") {
            try {
                $this->getForm();
            }
            catch (\Exception $e) {
                return '<div class="alert alert-danger">'.
                $e->getMessage() ."<br>\n".
                "File: <small>".$e->getFile() . "</small><br>\n".
                "Line: " . $e->getLine().'</div>';
            }
        }
        return $this->output;
    }

    /**
     * build form and check if process status is "success"
     * execute a callable
     *
     * @param callable $callable
     */
    public function saved(\Closure $callable)
    {
        $this->form_callable = $callable;
    }

    /**
     * alias for saved
     *
     * @param callable $callable
     */
    public function passed(\Closure $callable)
    {
        $this->saved($callable);
    }

    /**
     * append error (to be used in passed/saved closure)
     *
     * @param string $url
     * @param string $name
     * @param string $position
     * @param array  $attributes
     *
     * @return $this
     */
    public function error($error, $show_form = false)
    {
        $this->process_status = 'error';
        $this->message = ($show_form) ? '' : $error;
        if (!$show_form) $this->removeFieldType('submit');
        $this->message_class = 'alert alert-danger';
        $this->error .= $error;
        $this->errors[] = $error;
        return $this;
    }

    /**
     * replace form content with a message (error or success)
     * 
     * @param $message
     * @return $this
     */
    public function message($message)
    {
        $this->message =  $message;

        return $this;
    }

    /**
     * @param string $url
     * @param string $name
     * @param string $position
     * @param array  $attributes
     *
     * @return $this
     */
    public function link($url, $name, $position="BL", $attributes=array())
    {
        /*$match_url = trim(parse_url($url, PHP_URL_PATH),'/');
        if (Request::path()!= $match_url) {
            $url = Persistence::get($match_url);
        }*/

        $attributes = array_merge(array("class"=>"btn btn-default"), $attributes);
        $this->button_container[$position][] =  link_url($url, $name, $attributes);
        $this->links[] = $url;

        return $this;
    }

    /**
     * @param string $route
     * @param string $name
     * @param array  $parameters
     * @param string $position
     * @param array  $attributes
     *
     * @return $this
     */
    public function linkRoute($route, $name, $parameters=array(), $position="BL", $attributes=array())
    {
        return $this->link(link_route($route, $parameters), $name, $position, $attributes);
    }
}