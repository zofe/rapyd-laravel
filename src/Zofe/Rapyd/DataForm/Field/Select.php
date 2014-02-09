<?php namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Support\Facades\Form;

class Select extends Field
{

    public $type = "select";
    public $description = "";
    public $clause = "where";
    public $css_class = "select";


    function getValue()
    {
        parent::getValue();
        foreach ($this->options as $value => $description) {
            if ($this->value == $value) {
                $this->description = $description;
            }
        }
    }

    function build()
    {
        $output = "";
        if (!isset($this->style) AND !isset($this->attributes['style'])) {
            $this->style = "width:290px;";
        }
        unset($this->attributes['type'], $this->attributes['size']);
        if (parent::build() === false)
            return;

        switch ($this->status) {
            case "disabled":
            case "show":
                if (!isset($this->value)) {
                    $output = $this->layout['null_label'];
                } else {
                    $output = $this->description;
                }
                break;

            case "create":
            case "modify":
                $output = Form::select($this->name, $this->options, $this->value) . $this->extra_output;
                break;

            case "hidden":
                $output = Form::hidden($this->name, $this->value);
                break;

            default:
        }
        $this->output = $output;
    }

}
