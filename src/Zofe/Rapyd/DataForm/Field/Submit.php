<?php namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Support\Facades\Form;

class Submit extends Field {

  public $type = "submit";
  
  public function build()
  {
    $output = "";
    $this->attributes["class"] = "btn btn-default";
    if (parent::build() === false) return;

    switch ($this->status)
    {
      case "disabled":
      case "show":
		  
		if ($this->type =='hidden' || $this->value == "") {
          $output = "";
		} elseif ( (!isset($this->value)) )
        {
          $output = $this->layout['null_label'];
        } else {
          $output = nl2br(htmlspecialchars($this->value));
        }
        break;

      case "create":
      case "modify":
        $output = Form::submit($this->label, $this->attributes);
        break;
      default:;
    }
    $this->output = "\n".$output."\n". $this->extra_output."\n";
  }

}
