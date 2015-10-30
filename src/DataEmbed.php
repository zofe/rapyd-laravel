<?php namespace Zofe\Rapyd;


/**
 * Class DataEmbed
 * @package Zofe\Rapyd
 */
class DataEmbed
{
    public $output = "";
    public $url;
    public $id;
    
    public static function source($url, $id)
    {
        $ins = new static();
        $ins->url = $url;
        $ins->id = $id;
        return $ins;
    }

    public function build($view = 'rapyd::dataembed')
    {
        $url = $this->url;
        $id  = $this->id;
        \Rapyd::tag('tags/dataembed.html');
        $this->output = view($view, compact('url','id'))->render();
        
        return $this->output;
    }
    
    public function __toString()
    {
        if ($this->output == "") {
            $this->build();
        }

        return $this->output;
    }
}
