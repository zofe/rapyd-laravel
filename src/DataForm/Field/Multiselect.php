<?php namespace Zofe\Rapyd\DataForm\Field;

use Collective\Html\FormFacade as Form;

class Multiselect extends Field
{
    public $type = "checks";
    public $multiple = true;
    public $size = null;
    public $description = "";
    public $separator = "&nbsp;&nbsp;";
    public $serialization_sep = ",";
    public $format = "%s";
    public $css_class = "multiselect";
    public $checked_value = 1;
    public $unchecked_value = 0;
    public $clause = "wherein";

    public function getValue()
    {
        parent::getValue();

        if (is_array($this->value)) {
            $this->values = $this->value;
        } else {
            $this->values = explode($this->serialization_sep, $this->value);
        }

        $description_arr = array();
        foreach ($this->options as $value => $description) {
            if (in_array($value, $this->values)) {
                $description_arr[] = $description;
            }
        }
        $this->description = implode($this->separator, $description_arr);
    }

    public function build()
    {
        $output = "";

        if (!isset($this->style)) {
            $this->style = "margin:0 2px 0 0; vertical-align: middle";
        }
        unset($this->attributes['id']);
        if (parent::build() === false) {
            return;
        }

        switch ($this->status) {
            case "disabled":
            case "show":
                if (!isset($this->value)) {
                    $output = $this->layout['null_label'];
                } else {
                    $output = $this->description;
                }
                $output = "<div class='help-block'>" . $output . "&nbsp;</div>";
                break;

            case "create":
            case "modify":
                $this->attributes['multiple'] = 'multiple';
                $this->attributes['data-placeholder'] = $this->attributes['placeholder'];
                $this->attributes['placeholder'] = null;
                $output .= Form::select($this->name . '[]', $this->options, $this->values, $this->attributes);
                $output .= $this->extra_output;
                break;

            case "hidden":
                $output = Form::hidden($this->name, $this->value);
                break;

            default:
        }

        $this->output = $output;
    }
}
