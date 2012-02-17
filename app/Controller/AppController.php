<?php

class AppController extends Controller{

    public $components = array(
        'Session',
        'Auth' => array(
            'authenticate' => array(
                'Ldap',
                'Form'
            )
        )
    );
    public $helpers = array('Session', 'Form', 'Js' => array('Jquery'));

    function beforeFilter() {

        //clear authError default message
        $this->Auth->authError = " ";
    }


    function generateHash($size = 32) {

        return substr(md5(date('c')), 0, $size > 32 ? 32 : $size);
    }


    protected function isImage($file_type) {

        $valid = array('jpeg', 'png', 'gif');
        $extension = explode('/', $file_type);
        $extension = $extension[1];

        return in_array($extension, $valid);
    }
}
