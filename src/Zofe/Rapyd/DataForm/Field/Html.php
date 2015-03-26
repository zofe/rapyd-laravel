<?php namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Support\Facades\Form;

class Html extends Field
{
  public $type = "html";
  public $css_class = "textarea_html";
  
  public function build()
  {
    $output = "";

    if (parent::build() === false) return;

    switch ($this->status) {
      case "disabled":
      case "show":

        if ($this->type =='hidden' || $this->value == "") {
          $output = "";
        } elseif ( (!isset($this->value)) ) {
          $output = $this->layout['null_label'];
        } else {
          $output = '<div class="textarea_html_disabled"><pre>'.htmlspecialchars($this->value).'</pre></div>';
        }
        $output = "<div class='help-block'>".$output."&nbsp;</div>";
        break;

      case "create":
      case "modify":
        $output = Form::textarea($this->name, $this->value, $this->attributes);
        break;

      case "hidden":
        $output = Form::hidden($this->name, $this->value);
        break;

      default:;
    }
    $this->output = "\n".$output."\n". $this->extra_output."\n";
  }

}
