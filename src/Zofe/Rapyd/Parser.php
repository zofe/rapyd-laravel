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
            eval('?>'.$generated);
        }

        // If we caught an exception, we'll silently flush the output
        // buffer so that no partially rendered views get thrown out
        // to the client and confuse the user with junk.
        catch (\Exception $e)
        {
            ob_get_clean(); throw $e;
        }

        $content = ob_get_clean();

        return $content;
    }

}
