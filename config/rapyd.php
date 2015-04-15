<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Data Cell sanitization defaults
    |--------------------------------------------------------------------------
    */
    'sanitize' => [
        'num_characters' => 100, // Number of characters to return during cell value sanitization. 0 = no limit
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Class
    |--------------------------------------------------------------------------
    */
    'field'=> [
        'attributes' => ['class'=>'form-control'],
    ],
];
