<?php namespace Zofe\Rapyd;

class Widget
{

	public static $identifier = 0;
	public $label = "";
	public $output = "";
	public $built = FALSE;
    public $url;
	
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
		if (self::$identifier < 1)
		{
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
		if (preg_match_all('/\{(\w+)\}/is', $pattern, $matches))
		{
			return $matches[1];
		}
	}

    
	/**
	 * dynamic getter & setter 
	 * 
	 * it's used basically to ensure method chaining and to get & set widget's properties
	 * it also enable "short array" syntax  so you can use $widget->method('param|param') and it will call
	 * $widget->setMethod('param','param')
	 * 
	 * @param string $method
	 * @param array $arguments
	 * @return object $this 
	 */
	/*public function __call($method, $arguments)
	{
		$prefix = strtolower(substr($method, 0, 3));
		$property = strtolower(substr($method, 3));
		if (method_exists($this, 'set' . ucfirst($method)))
		{
			return call_user_func_array(array($this, 'set' . ucfirst($method)), $arguments);
		}

		if (empty($prefix) || empty($property))
		{
			return;
		}

		if ($prefix == "get" && isset($this->$property))
		{
			return $this->$property;
		}

		if ($prefix == "set")
		{
			if
			(
					!in_array($property, array('cell_template', 'pattern'))
					AND is_string($arguments[0])
					AND strpos($arguments[0], '|')
			)
			{
				$this->$property = explode('|', $arguments[0]);
			} else
			{
				$this->$property = $arguments[0];
			}
			return $this;
		}
	}*/
    
	/**
	 * "echo $widget" automatically call build() it and display $widget->output
	 * however explicit build is preferred for a clean code
	 * 
	 * @return string 
	 */
	function __toString()
	{
		if ($this->output == "")
			$this->build();
		return $this->output;
	}

}