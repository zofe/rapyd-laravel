<?php namespace Zofe\Rapyd\DataGrid;

use Zofe\Rapyd\Widget;

class Column extends Widget
{

    public $url = "";
    
    
    public $link = "";
    public $linkRoute = "";
    
    public $label = "";
    public $attributes = array();
    public $tr_attributes = array();
    public $tr_attr = array();
    public $orderby = null;
    public $orderby_asc_url;
    public $orderby_desc_url;
    protected $pattern = "";
    protected $pattern_type = null;
    protected $row_as = null;
    protected $field = null;
    protected $field_name = null;
    protected $field_list = array();

    public function __construct($name, $label = null, $orderby = false)
    {
        $this->name = $name;
        $this->label($label);
        $this->orderby($orderby);
    }

    protected function label($label)
    {
        $this->label = $label;
    }

    protected function orderby($orderby)
    {
        $this->orderby = $orderby;
        return $this;
    }

    public function link($url, $name, $position = 'BL', $attributes = array())
    {
        $this->link = $url;
        return $this;
    }
     
    public function attributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }
    
}
