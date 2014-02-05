<?php namespace Zofe\Rapyd;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\HTML;

class Rapyd
{

    protected static $container;
    protected static $js = array();
    protected static $css = array();

    /**
     * Bind a Container to Rapyd
     *
     * @param Container $container
     */
    public static function setContainer(Container $container)
    {
        static::$container = $container;
    }

    /**
     * Get the Container from Rapyd
     *
     * @param string $make A dependency to make on the fly
     * @return Container
     */
    public static function getContainer($make = null)
    {
        if ($make) {
            return static::$container->make($make);
        }

        return static::$container;
    }

    public static function head()
    {
        $buffer = "\n";

        //css links
        foreach (self::$css as $item) {
            $buffer .= HTML::style($item);
        }
        //js links
        foreach (self::$js as $item) {
            $buffer .= HTML::script($item);
        }
        return $buffer;
    }

    public static function js($js)
    {
        if (!in_array($js, self::$js))
            self::$js[] = $js;
    }

    public static function css($css)
    {
        if (!in_array($css, self::$css))
            self::$css[] = $css;
    }

    public static function script($script)
    {
        return sprintf("\n<script language=\"javascript\" type=\"text/javascript\">\n %s \n</script>\n", $script);
    }

    public static function style($style)
    {
        return sprintf("<style type=\"text/css\">\n%s\n</style>", $style);
    }

}