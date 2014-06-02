<?php if (!defined('RAPYD_PATH')) exit('No direct script access allowed');

class checkbox_group_field extends field_field {

  public $type = "checks";
  public $size = null;
  public $description = "";
  public $separator = "&nbsp;&nbsp;";
  public $format = "%s";
  public $css_class = "checkbox";
  public $checked_value = 1;
  public $unchecked_value = 0;
  public $option_groups = array();
  public $format_group = '<div class="ceckbox_group">
                            <div class="ceckbox_group_label">%s  %s</div>
                            <div>%s</div>
                            <div style="clear:left"></div>
                          </div>';

  function get_value()
  {
    parent::get_value();

    /*if ($this->options_table=="")
    {

    }*/
    $this->values = explode($this->serialization_sep, $this->value);
    //var_dump($this->value);
    $description_arr = array();
    foreach ($this->options as $value=>$description)
    {
      if (in_array($value,$this->values))
      {
        $description_arr[] = $description;
      }
    }
    $this->description = implode($this->separator, $description_arr);
  }

  function build()
  {
    $output = "";

    if(!isset($this->style))
    {
      $this->style = "margin:0 2px 0 0; vertical-align: middle";
    }
    unset($this->attributes['id']);
    if (parent::build() === false) return;



    switch ($this->status)
    {
      case "disabled":
      case "show":
        if (!isset($this->value))
        {
          $output = $this->layout['null_label'];
        } else {
           $output = $this->description;
        }
        break;

      case "create":
      case "modify":

        $i = 1;
        if (count($this->option_groups))
        {
            $output = '';
            foreach ($this->option_groups as $group_id => $group )
            {
                $group_output = '';
                foreach ($group['options'] as $val => $label )
                {
                  $attributes = $this->attributes;
                  $attributes['name'] = $this->name.'[]';
                  $attributes['id'] = $this->name.'_'.$i++;
                  $this->checked = in_array($val,$this->values);
                  $group_output .= sprintf($this->format, rpd_form_helper::checkbox($attributes, $val , $this->checked).$label).$this->separator;
                }

                $output .= sprintf($this->format_group, $group['label'], rpd_html_helper::image('checked.png',array('class'=>'group_check')).' '.rpd_html_helper::image('unchecked.png',array('class'=>'group_uncheck')) ,$group_output). $this->extra_output();
                $output .= rpd_html_helper::script("
                    $('.group_check').click(function(){
                          $(this).parent().parent().find(\"input[type='checkbox']\").attr('checked', true);
                    });
                    $('.group_uncheck').click(function(){
                          $(this).parent().parent().find(\"input[type='checkbox']\").attr('checked', false);
                    });
                ");
            }
        } else {

            foreach ($this->options as $val => $label )
            {
              $attributes = $this->attributes;
              $attributes['name'] = $this->name.'[]';
              $attributes['id'] = $this->name.'_'.$i++;
              $this->checked = in_array($val,$this->values);
              $output .= sprintf($this->format, rpd_form_helper::checkbox($attributes, $val , $this->checked).$label).$this->separator;
            }
            $output .=  $this->extra_output();
        }
        break;

      case "hidden":
        $output = rpd_form_helper::hidden($this->name, $this->value);
        break;

      default:
    }
    $this->output = $output;
  }


}
