<?php namespace Zofe\Rapyd\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class AjaxController extends Controller
{
    public function getRemote($hash)
    {
       if (Session::has($hash)) {
           $ajax = Session::get($hash);
           $entity = $ajax['entity'];
           $field =  $ajax['field'];
           $field = (array) $field;

           $f = array_shift($field);
           $query = $entity::where($f,"like", "%" . Input::get("q") . "%");

           if (count($field)) {
               foreach ($field as $f) {
                   $query = $query->orWhere($f,"like", "%" . Input::get("q") . "%");
               }

           }
           //reduce http response size & avoid security data leak issue
           $return_fields = array_merge((array)$ajax['field'], ['id']);

           return $query->take(10)->get($return_fields);
       }

    }
}
