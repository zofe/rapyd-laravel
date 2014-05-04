<?php namespace Zofe\Rapyd;

use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\App;
class Widget
{

    public static $identifier = 0;
    public $label = "";
    public $output = "";
    public $built = FALSE;
    public $url;

    public $process_status = "idle";
    public $status = "idle";
    public $action = "idle";
    
    public $button_container = array( "TR"=>array(), "BL"=>array(), "BR"=>array() );
    public $message = "";

    public function __construct()
    {
        $this->url = new Url();
        $this->parser = new Parser(App::make('files'), App::make('path').'/storage/views');
    }

    /**
     * identifier is empty or a numeric value, it "identify" a single object instance.
     * 
     * @return string identifier 
     */
    protected function getIdentifier()
    {
        if (static::$identifier < 1) {
            static::$identifier++;
            return "";
        }
        return (string) static::$identifier++;
    }

    /**
     * @param string $name
     * @param string $position
     * @param array  $attributes
     *
     * @return $this
     */
    function button($name, $position="BL", $attributes=array())
    {
        $attributes = array_merge(array("class"=>"btn btn-default"), $attributes);
        
        $this->button_container[$position][] = Form::button($name, $attributes);

        return $this;
    }

    /**
     * @param string $url
     * @param string $name
     * @param string $position
     * @param array  $attributes
     *
     * @return $this
     */
    function link($url, $name, $position="BL", $attributes=array())
    {
        $attributes = array_merge(array("class"=>"btn btn-default"), $attributes);
        $this->button_container[$position][] =  Html::link($url, $name, $attributes);

        return $this;
    }

    /**
     * @param string $route
     * @param string $name
     * @param array  $parameters
     * @param string $position
     * @param array  $attributes
     *
     * @return $this
     */
    function linkRoute($route, $name, $parameters=array(), $position="BL", $attributes=array())
    {
        $attributes = array_merge(array("class"=>"btn btn-default"), $attributes);
        $this->button_container[$position][] = Html::linkRoute($route, $name, $parameters, $attributes);
        return $this;
    }

    /**
     * @param string $url
     * @param string $name
     * @param string $position
     * @param array  $attributes
     *
     * @return $this
     */
    function message($message)
    {
        $this->message =  $message;
        return $this;
    }

    /**
     * "echo $widget" automatically call build() it and display $widget->output
     * however explicit build is preferred for a clean code
     * 
     * @return string 
     */
    public function __toString()
    {
        if ($this->output == "")
        {    
            $this->build();
        }
        return $this->output;
    }

}
