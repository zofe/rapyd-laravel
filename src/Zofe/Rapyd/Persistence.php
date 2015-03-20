<?php namespace Zofe\Rapyd;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class Persistence
{

    public static function get($url)
    {
        return Session::get('rapyd.' . $url, $url);

    }

    public static function save()
    {

        Session::put('rapyd.' . Request::path(), Request::fullUrl());
    }

    public static function clear()
    {
        Session::forget('rapyd.' . Request::path());
    }

}
