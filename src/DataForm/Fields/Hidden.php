<?php namespace Zofe\Rapyd\DataForm\Fields;

use Zofe\Rapyd\DataForm\Form;

class Hidden extends Field
{
    public $type = "hidden";

    public function edit()
    {
        return Form::hidden($this->name, $this->value);
    }

    public function show()
    {
        return '';
    }
}
