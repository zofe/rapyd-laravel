<?php  namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Support\Facades\Form;

class Radiogroup extends Field
{

    public $type = "radio";
    public $size = null;
    public $description = "";
    public $separator = "&nbsp;&nbsp;";
    public $clause = "where";

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
                $output = "<div class='help-block'>".$output."</div>";
                break;

            case "create":
            case "modify":

                foreach ($this->options as $val => $label) {
                    $this->checked = (!is_null($this->value) AND ($this->value == $val));
                    $output .= Form::radio($this->name, $val, $this->checked).' '. $label. $this->separator;
                }
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
