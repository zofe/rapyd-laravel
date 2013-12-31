<?php namespace Zofe\Rapyd;

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
    
    public function __construct()
    {
        $this->url = new Url();
    }

    /**
     * identifier is empty or a numeric value, it "identify" a single object instance.
     * 
     * @return string identifier 
     */
    protected function getIdentifier()
    {
        if (self::$identifier < 1) {
            self::$identifier++;
            return "";
        }
        return (string) self::$identifier++;
    }

    /**
     * {placeholder} support for pesentation widgets
     * parse_pattern find all occurences of holders and return a simple array of matches
     * it's used for example to find "field" placeholders inside a datagrid column pattern
     * 
     * @param string $pattern
     * @return array of matches {placeholders} 
     */
    public static function parse_pattern($pattern)
    {
        if (preg_match_all('/\{(\w+)\}/is', $pattern, $matches)) {
            return $matches[1];
        }
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
