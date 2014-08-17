<?php namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Support\Facades\Form;

class Submit extends Field
{
  public $type = "submit";
  public $attributes = array('class' => 'btn btn-default');

  public function build()
  {
    $output = "";
    if (parent::build() === false) return;

    switch ($this->status) {
      case "disabled":
          $output = '';
        break;
      case "show":
      case "create":
      case "modify":
        $output = Form::submit($this->label, $this->attributes);
        break;
      default:;
    }
    $this->output = "\n".$output."\n". $this->extra_output."\n";
  }

}
