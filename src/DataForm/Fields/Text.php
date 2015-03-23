<?php namespace Zofe\Rapyd\DataForm\Fields;

use Zofe\Rapyd\DataForm\Form;

class Text extends Field
{
    public $type = "text";

    public function edit() {
        return Form::text($this->name, $this->value, $this->attributes);
    }

}