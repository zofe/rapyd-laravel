<?php


Route::get('rapyd-ajax/{hash}', array('as' => 'rapyd.remote', 'uses' => '\Zofe\Rapyd\Controllers\AjaxController@getRemote'));
Route::controller('rapyd-demo','\Zofe\Rapyd\Demo\DemoController');

