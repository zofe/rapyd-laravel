<?php namespace Zofe\Rapyd;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

// to be continued using laravel facades Input - Session  instead of globals...


class Persistence {


  public static function get()
  {
    $self = Request::url();
    return Session::get('rapyd.'.$self, array());
  }

  public static function save()
  {
    $self = $_SERVER['PHP_SELF'];
    $page = self::get();

    if (count($_POST)<1)
    {
      if ( isset($page["back_post"]) )
      {
        $_POST = $page["back_post"];
      }
    } else {
      $page["back_post"]= $_POST;
    }

    $page["back_url"]= Request::url();
    $_SESSION['rapyd'][$self] = $page;
  }

	// --------------------------------------------------------------------

  public static function clear()
  {
    $self = $_SERVER['PHP_SELF'];
    unset($_SESSION['rapyd'][$self]);
  }



}
