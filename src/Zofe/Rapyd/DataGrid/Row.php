<?php namespace Zofe\Rapyd\DataGrid;

use Zofe\Rapyd\Helpers\HTML;

class Row
{
    public $attributes = array();
    public $cells = array();


    public function add(Cell $cell)
    {
        $this->cells[] = $cell;
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
