<?php

//due security reason leave this commented
//and tell user to enable it on \app\routes.php on README.md 
//Route::controller('rapyd-demo', 'Zofe\\Rapyd\\Controllers\\DemoController');


Route::get('rapyd-ajax/{hash}', array('as' => 'rapyd.remote', 'uses' => 'Zofe\Rapyd\Controllers\AjaxController@getRemote'));