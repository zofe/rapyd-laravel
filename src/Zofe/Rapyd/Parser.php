<?php namespace Zofe\Rapyd;

use Illuminate\View\Compilers\BladeCompiler;

//http://stackoverflow.com/questions/16891398/is-there-anyway-around-to-compile-blade-template-like-this
    
class Parser extends BladeCompiler {

    /**
     * Compile blade template with passing arguments.
     *
     * @param  [type] $value [description]
     * @param  array  $args  [description]
     * @return [type]        [description]
     */
    public function compileString($value, array $args = array())
    {
        $generated = parent::compileString($value);

        ob_start() and extract($args, EXTR_SKIP);

        // We'll include the view contents for parsing within a catcher
        // so we can avoid any WSOD errors. If an exception occurs we
        // will throw it out to the exception handler.
        try
        {
            eval('?>'.$generated.'<?php ');
        }

        // If we caught an exception,  return string as is
        catch (\Exception $e)
        {
            ob_get_clean(); //throw $e;
            return $value;
        }

        $content = ob_get_clean();

        return $content;
    }

}
