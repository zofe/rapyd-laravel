<?php namespace Zofe\Rapyd;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Input;


class Persistence {

  
  public static function all()
  {
    $self = strtok(Request::server('REQUEST_URI'),'?');
    return Session::get('rapyd.'.$self, array());
  }
  
  public static function get($key)
  {
    /*Session::flush();
    Session::forget('rapyd');
    Session::forget('rapyd/index');
    Session::forget('rapyd/rapyd/grid');*/
    $self = strtok(Request::server('REQUEST_URI'),'?');
   
    //var_export($_POST);
    
    //echo "\n\n";
    //var_export(Session::get('rapyd.'.$self.".back_post.".$key)); 
    //die;

    return Session::get('rapyd.'.$self.".back_post.".$key, Input::get($key));
      
  }

  public static function save()
  {

    $self = strtok(Request::server('REQUEST_URI'),'?');
    $page = self::all();
    $page["back_post"]= Input::all();
    $page["back_url"]= Request::url();
    Session::put('rapyd.'.$self, $page);
    
    //var_export(Session::all());
    //echo "\n\n..";
    //var_export(Session::get('rapyd.'.$self.".back_post".".nome")); 
   //die;
  }

  public static function clear()
  {
    $self = strtok(Request::server('REQUEST_URI'),'?');
    Session::forget('rapyd.'.$self);
  }



}
