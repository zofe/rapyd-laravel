<?php


Route::get('rapyd-ajax/{hash}', array('as' => 'rapyd.remote', 'uses' => 'Zofe\Rapyd\Controllers\AjaxController@getRemote'));
