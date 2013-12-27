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
        $this->orderby($label);
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

    protected function url($url, $img = '')
    {
        $this->url = $url;
        $this->img = $img;
        return $this;
    }

    /*
    public function setRow($data_row)
    {
        if (isset($data_row[$this->pattern])) {
            $this->rpattern = $data_row[$this->pattern];
        } else {
            if (isset($this->row_as)) {
                $data_row =  array($this->row_as => $data_row);
            } else {
                $data_row = get_object_vars($data_row);
            }
            $this->rpattern = $this->parser->render($this->pattern, $data_row);
        }
        
        if (isset($this->callback_object)) {
            $this->rpattern = call_user_func(array($this->callback_object, $this->callback), $data_row);
        } elseif (isset($this->callback)) {
            $this->rpattern = call_user_func($this->callback, $data_row);
        }
        if ($this->url) {
            if (!isset($this->attributes['style']))
                $this->attributes['style'] = 'width: 70px; text-align:center; padding-right:5px';
            $this->link = $this->parser->render($this->url, $data_row);
        }

        //manage attributes
    }
    */
    
    public function attributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }
    
}
