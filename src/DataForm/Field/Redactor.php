<?php namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Html\FormFacade as Form;
use Zofe\Rapyd\Rapyd;
class Redactor extends Field
{
  public $type = "text";
  public $upload_url = null; 

  public function upload($url)
  {
      $this->upload_url = $url;

      return $this;
  }

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
          $output = nl2br(htmlspecialchars($this->value));
        }
        $output = "<div class='help-block'>".$output."&nbsp;</div>";
        break;

      case "create":
      case "modify":

        Rapyd::js('redactor/jquery.browser.min.js');
        Rapyd::js('redactor/redactor.min.js');
        Rapyd::css('redactor/css/redactor.css');
        $output  = Form::textarea($this->name, $this->value, $this->attributes);

		$redactor_params = '';

		if ($this->upload_url)
		{
			$redactor_params = "imageUpload: '" . $this->upload_url . "?_token=" . csrf_token() . "'";
		}

        Rapyd::script("$('#".$this->name."').redactor({".$redactor_params."});");

        break;

      case "hidden":
        $output = Form::hidden($this->name, $this->value);
        break;

      default:;
    }
    $this->output = "\n".$output."\n". $this->extra_output."\n";
  }

}
