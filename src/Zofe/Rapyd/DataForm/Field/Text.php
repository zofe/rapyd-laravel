<?php namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Support\Facades\Form;

class Text extends Field {

  public $type = "text";
  
  public function build()
  {
    $output = "";
    $this->attributes["class"] = "form-control";
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
        $output = "<div class='help-block'>".$output."</div>";
        break;

      case "create":
      case "modify":
        $output = Form::text($this->db_name, $this->value, $this->attributes);
        break;

      case "hidden":
        $output = Form::hidden($this->db_name, $this->value);
        break;

      default:;
    }
    $this->output = "\n".$output."\n". $this->extra_output."\n";
  }

}
