<?php

namespace Zofe\Rapyd\DataForm\Field;

use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Input;

class Container extends Field
{
    public $type = "container";
    public $pattern = '';
    public $is_view = false;
    
    
    public function autoUpdate($save = false)
    {
        $this->getValue();
        return true;
    }

    public function content($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }
    public function view($view)
    {
        $this->pattern = $view;
        $this->is_view = true;
        return $this;
    }
    
    public function build()
    {
        $output = "";
        
        if (parent::build() === false) return;

        switch ($this->status) {
            case "disabled":
            case "show":
            case "create":
            case "modify":
                    $output = '<div>'.$this->parseString($this->pattern, $this->is_view).'</div>';
                break;
            case "hidden":
                $output = "";
                break;

            default:;
        }
        $this->output = "\n".$output."\n". $this->extra_output."\n";
    }
}
