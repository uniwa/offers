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

    public $helpers = array(
        'Session',
        'Form',
        'Html',
        'Js' => array('Jquery'),
        'Tb' => array('className' => 'TwitterBootstrap.TwitterBootstrap')
    );

    function beforeFilter() {
        $guest = is_null($this->Auth->user());
        $this->set('guest', $guest);
        //clear authError default message
        $this->Auth->authError = " ";

        // When logged user has not accepted terms,
        // redirect to terms of use (only allow logout)
        $cur_user = $this->Auth->user();
        if (!is_null($cur_user)) {
            if (($cur_user['role'] === ROLE_STUDENT) && !($this->request['controller'] == 'users' && $this->request['action'] == 'terms')){
                if (!$cur_user['terms_accepted']) {
                    if (!($this->request['controller'] == 'users' && $this->request['action'] == 'logout')) {
                        $this->redirect(array('controller' => 'users', 'action' => 'terms'));
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
