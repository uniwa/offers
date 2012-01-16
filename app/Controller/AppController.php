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

        $size = $size - 1;
        if ($size > 31) $size = 31;

        $hash = md5(date('c'));
        $hash = substr($hash, 0, $size);

        return $hash;
    }
}
