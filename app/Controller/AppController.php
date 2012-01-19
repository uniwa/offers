<?php

class AppController extends Controller{

    public $components = array(
        'Session',
        'Auth' => array(
            'authenticate' => array('Form')
        )
    );

    function beforeFilter() {

        //clear authError default message
        $this->Auth->authError = " ";
    }

    function generateHash($size = 32) {

        return substr(md5(date('c')), 0, $size > 32 ? 32 : $size);
    }
}
