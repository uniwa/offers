<?php

App::uses('ImageExtensionException', 'Error');
App::uses('UploadFileException', 'Error');

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
        
        // When logged user has not accepted terms,
        // redirect to TermsOfUse (only allow logout)
        $cur_user = $this->Auth->user();
        if($cur_user != null) {
            if(!($this->request['controller'] == 'TermsOfUse')) { 
                if(!($this->request['controller'] == 'users' && $this->request['action'] == 'logout')) {
                    if(!$cur_user['terms_accepted']){
                        $this->redirect( array( 'controller'=>'TermsOfUse', 'action'=>'index' ) );
                    }
                }
            }
        }
    }

    public function is_authorized($user) {
        // main authorization function
        // override in each controller

        // admin can access every action
        if (isset($user['role']) && $user['role'] === ROLE_ADMIN) {
            return true;
        }

        // default deny
        return false;
    }

}
