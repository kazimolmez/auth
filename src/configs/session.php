<?php

/**
 * @author  Samir Rustamov <rustemovv96@gmail.com>
 * @link 	https://github.com/SamirRustamov/Auth
 */





return array(

    /*
    |---------------------------------------------------------
    | Session Driver
    |---------------------------------------------------------
    |
    */
    'driver'          => "file",


    /*
    |---------------------------------------------------------
    | Session Files Location
    |---------------------------------------------------------
    */
    'file_location'   => dirname(__DIR__).'/Libraries/Session/sessions',




    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    */

    'lifetime'        => 3600,


    /*
    |--------------------------------------------------------------------------
    | Cookies
    |--------------------------------------------------------------------------
    */

    'only_cookies' => true,



    'cookie' => array(

        'name' => 'TT_AUTH_SESSION',

        'path' => '/',

        'secure' => false,

        'domain' => null,

        'http_only' => true,

     ),





);
