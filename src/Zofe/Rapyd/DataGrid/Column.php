<?php namespace Zofe\Rapyd\DataGrid;

use Zofe\Rapyd\Widget;

class Column extends Widget
{

    public $url = "";
    public $link = "";
    public $label = "";
    public $attributes = array();
    public $tr_attributes = array();
    public $tr_attr = array();
    public $column_type = "normal"; //orderby, detail
    public $orderby = null;
    public $orderby_asc_url;
    public $orderby_desc_url;
    protected $pattern = "";
    protected $pattern_type = null;
    protected $row_as = null;
    protected $field = null;
    protected $field_name = null;
    protected $field_list = array();

    public function __construct()
    {
        $this->check_pattern();
    }


    protected function label($label)
    {
        $this->label = $label;
    }

    public function orderby($orderby)
    {
        $this->orderby = $orderby;
        return $this;
    }

    protected function url($url, $img = '')
    {
        $this->url = $url;
        $this->img = $img;
        return $this;
    }

    public function attributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }
    
}
