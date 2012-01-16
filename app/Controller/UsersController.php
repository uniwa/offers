<?php

class UsersController extends AppController {

    function beforeFilter() {
       // parent::bforeFilter();

        $this->Auth->allow('register');

    }

    function login() {

        if( $this->request->is( 'post' ) ) {
            if( $this->Auth->login() ) {

                return $this->redirect( $this->Auth->redirect() );
            } else {

                $this->Session->setFlash(__("Δώστε έγκυρο όνομα και κωδικό χρήστη"), 'default', array(), 'auth' );  
            }
        }
    }

    function logout() {

        $this->redirect( $this->Auth->logout() );

    }

    function register() {

        if( $this->request->is('post') ) {

            if( !empty( $this->request->data ) ) {

                //is_enabled aand is_banned is by default false
                //set registered User's role
                $this->request->data['User']['role'] =  'company';
                //Use this to avoid valdation errors
                unset($this->User->Company->validate['user_id']);
                if( $this->User->saveAssociated($this->request->data) ){
                    $this->Session->setFlash(__('Η εγγραφή ολοκληρώθηκε') );
                    $this->redirect(array('action' => 'index'));
                }
            } else {

                $this->Session->setFlash(__('Η εγγραφή δεν ολοκληρώθηκε'));
            }
        }
    }
}
