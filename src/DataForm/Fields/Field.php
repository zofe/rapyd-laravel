<?php namespace Zofe\Rapyd\DataForm\Fields;

use Zofe\Rapyd\DataForm\Form;

class Field
{
    public $type = "field";
    public $multiple = false;
    public $visible = true;
    public $status = "edit";

    public $name;
    public $label;
    public $value = null;
    public $default_value;
    public $options = array();
    public $rule = '';
    public $req = '';
    public $messages = array();
    public $attributes = array('class'=>'form-control');
    public $output = '';
    public $has_error = '';
    public $request_refill = true;

    /**
     * set value (override it in fields to format) 
     * 
     * @param $value
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * set options array (for select, checkboxgroup, etc..)
     * 
     * @param $options
     * @return $this
     */
    public function options($options)
    {
        if (is_array($options)) {
            $this->options += $options;
        }

        return $this;
    }

    /**
     * append single option: value and description (for select, checkboxgroup, etc..)
     * 
     * @param string $value
     * @param string $description
     * @return $this
     */
    public function option($value = '', $description = '')
    {
        $this->options[$value] = $description;

        return $this;
    }
    
    /**
     * get value
     * 
     * @return null
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * add rules for field es.:  required|min:5 ...
     * @param $rule
     * @return $this
     */
    public function rule($rule)
    {
        $this->rule = trim($this->rule."|".$rule, "|");
        if ((strpos($this->rule, "required") !== false) and !isset($this->no_star)) {
            $this->required = true;
        }

        return $this;
    }

    /**
     * set attributes for widget
     * @param $attributes
     * @return $this
     */
    public function attributes($attributes)
    {
        if (is_array($this->attributes) and is_array($attributes)) {
            $attributes = array_merge($this->attributes, $attributes);
        }
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * set default value
     * 
     * @param $value
     * @return $this
     */
    public function value($value)
    {
        $this->default_value = $value;
        return $this;
    }
    
    
    /**
     * display field on "edit" status
     * 
     * @return string
     */
    public function edit() {
        return Form::text($this->name, $this->value, $this->attributes);
    }

    /**
     * display field on "hide" status
     * 
     * @return string
     */
    public function hide() {
        return  Form::hidden($this->name, $this->value);
    }

    /**
     * display value on "show" status
     * 
     * @return mixed
     */
    public function show() {
        return $this->value;
    }

    public function build() {
        
        if (($this->status == "hidden" || $this->visible === false || in_array($this->type, array("hidden", "auto")))) {
            $this->is_hidden = true;
        }
        $this->message = implode("<br />\n", $this->messages);


        if ($this->orientation == 'inline') {
            $this->attributes["placeholder"] = $this->label;
        }

        if ($this->visible === false) {
            return false;
        }
        
        $this->output = $this->make($this->status);
    }

    public function all()
    {
        $output  = "<label for=\"{$this->name}\" class=\"{$this->req}\">{$this->label}</label>";
        $output .= $this->output;
        $output  = '<span id="div_'.$this->name.'">'.$output.'</span>';
        if ($this->has_error) {
            $output = "<span class=\"has-error\">{$output}<span class=\"help-block\"><span class=\"glyphicon glyphicon-warning-sign\"></span> {$this->message}</span></span>";
        }

        return $output;
    }
    
    protected function make($status)
    {
        return $this->$status();
    }
}