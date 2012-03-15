<?php

App::uses('ImageExtensionException', 'Error');
App::uses('UploadFileException', 'Error');

App::uses('Image', 'Model');

App::import('Vendor', 'OfferStates');

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
    public $helpers = array('Session', 'Form', 'Js' => array('Jquery'), 'Html');

    //constant variables for flashes
    protected $INFO = 'alert alert-info';
    protected $ERROR = 'alert alert-error';
    protected $SUCCESS = 'alert alert-success';

    function beforeFilter() {

        //clear authError default message
        $this->Auth->authError = " ";
    }


    protected function generateHash($size = 32) {

        return substr(md5(date('c')), 0, $size > 32 ? 32 : $size);
    }
}
