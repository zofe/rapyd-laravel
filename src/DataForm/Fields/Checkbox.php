<?php namespace Zofe\Rapyd\DataForm\Fields;

use Zofe\Rapyd\DataForm\Form;

class Checkbox extends Field
{
    public $type = "checkbox";
    public $checked = false;
    public $checked_value = 1;
    public $unchecked_value = 0;
    public $checked_output = 'yes';
    public $unchecked_output = 'no';
    
    public function setValue($value) {


        if ($value == $this->checked_value) {
            $this->checked = true;
            $this->value = $this->checked_value;
        } else {
            $this->value = $this->unchecked_value;
        }
    }
    
    public function edit() {
        return Form::checkbox($this->name, $this->checked_value, $this->checked, $this->attributes);
    }

}