<?php

App::uses('ImageExtensionException', 'Error');
App::uses('UploadFileException', 'Error');
App::uses('Image', 'Model');

App::import('Vendor', 'OfferStates');
App::import('Vendor', 'Flash');

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

    function beforeFilter() {
        //clear authError default message
        $this->Auth->authError = " ";

        if(  $this->Auth->user() != null ) {
            if(!($this->request['controller'] == 'TermsOfUse' && $this->request['action'] == 'index' )) { 
                if( !($this->request['controller'] == 'Offers' && $this->request['action'] == 'index') ) {
                   if( !($this->request['controller'] == 'Users' && $this->request['action'] == 'logout') ) {

                    $this->redirect( array( 'controller'=>'TermsOfUse', 'action'=>'index' ) );
                    }
                
                }
            }
        } 
    }

}
