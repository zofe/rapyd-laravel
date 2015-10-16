<?php namespace Zofe\Rapyd;


/**
 * Class DataEmbed
 * @package Zofe\Rapyd
 */
class DataEmbed
{
    public static function source($url, $id)
    {
        return view('rapyd::dataembed', compact('url','id'));
    }
}
