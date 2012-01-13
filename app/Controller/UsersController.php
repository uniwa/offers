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

        if( $this->request->is('post') ) 
        {
            $this->User->create();
            if( $this->User->save($this->request->data) ) {
                $this->Session->setFlash(__('Η εγγραφή ολοκληρώθηκε') );
                $this->redirect(array('action' => 'index'));
                $this->Auth->login( $this->request->data['User']);
                $this->redirect('/user/home');
            } else {

                $this->Session->setFlash(__('Η εγγραφή δεν μπορεί να ολοκληρωθεί'));
            }
        }
    }
}
