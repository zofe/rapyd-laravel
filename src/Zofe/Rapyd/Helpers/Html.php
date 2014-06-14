<?php namespace Zofe\Rapyd\Helpers;


class HTML
{

    public static function buildAttributes(array $attributes = null)
    {
        if (empty($attributes))
            return '';

        $compiled = '';
        foreach ($attributes as $key => $val) {
            $compiled .= ' ' . $key . '="' . HTML::chars($val) . '"';
        }
        return $compiled;
    }

    public static function chars($value, $double_encode = TRUE)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8", $double_encode);
    }

} 