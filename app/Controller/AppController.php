<?php

class AppController extends Controller{

    public $components = array(

        'Session',
        'Auth' => array(
            'authenticate' => array(
                'Form' => array(
                    'scope' => array( 'User.is_banned' => 0 )
                )
            )
        )
    );

    function generateHash($size = 32) {

        if ($size > 32) $size = 32;

        $hash = md5(date('c'));
        $hash = substr($hash, 0, $size);

        return $hash;
    }
}
