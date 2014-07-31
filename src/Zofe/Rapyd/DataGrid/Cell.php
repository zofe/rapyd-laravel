<?php namespace Zofe\Rapyd\DataGrid;

use Zofe\Rapyd\Helpers\HTML;

class Cell
{
    public $name = null;
    public $attributes = array();
    public $value = null;

    public function __construct($name)
    {
        $this->name = $name;
    }
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

    public function parseFilters($filters)
    {
        if (count($filters) < 1)
            return false;
            
        foreach ($filters as $filter)
        {
            $params = array();
            if (preg_match('/([^\[]*+)\[(.+)\]/', $filter, $match))
            {
                $filter   = $match[1];
                $params = explode(',', $match[2]);
            }

            if (function_exists($filter))
            {
                if (count($params)<1) {
                    $this->value = $filter($this->value);
                } else {

                    //assuming that value is the first param, 
                    //we should add fixes for most common php functions where it isn't in head.
                    
                    $value = $this->value;
                    array_unshift($params, $value);
                    try {
                        $this->value = call_user_func_array($filter, $params);
                    } catch (\Exception $e) {

                    }

                }

            }

        }

    }
    
    public function __toString()
    {
        return $this->value;
    }

}
