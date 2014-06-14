<?php namespace Zofe\Rapyd\DataGrid;

use Zofe\Rapyd\Helpers\HTML;

class Cell
{
    public $attributes = array();
    public $value = null;

    public function value($value)
    {
        $this->value = $value;
        return $this;
    }
    
    public function attributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }
    
    public function style($style)
    {
        $this->attributes['style'] = $style;
        return $this;
    }
    
    public function buildAttributes()
    {
        return HTML::buildAttributes($this->attributes);
    }

}
