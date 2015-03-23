<?php namespace Zofe\Rapyd\DataForm\Fields;

use Zofe\Rapyd\DataForm\Form;

class Password extends Field
{
    public $type = "password";

    public function edit()
    {
        return Form::password($this->name, $this->value, $this->attributes);
    }

    public function show()
    {
        if ((!isset($this->value))) {
            $output = $this->layout['null_label'];
        } else {
            $output = "********";
        }
        return $output;
    }
}
