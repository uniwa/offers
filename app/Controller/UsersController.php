<?php

class UsersController extends AppController {

    function beforeFilter() {
       // parent::bforeFilter();

        $this->Auth->allow('register');

    }

    function login() {

        if( $this->request->is( 'post' ) ) {

            if( $this->Auth->login() && $this->isCompanyEnabled( $this->request->data ) ) {
           
                return $this->redirect( $this->Auth->redirect() );
            } else {

                $this->Session->setFlash(__("Δώστε έγκυρο όνομα και κωδικό χρήστη"), 'default', array(), 'auth' );  
            }
        }
    }

    private function isCompanyEnabled( $data ) {

        $username = $data['User']['username'];
        $currentUser = $this->User->find( 'all', 
            array( 'conditions' => array( 'username' => $username ) )
        );


        //checks if user is not company owner 
        $notCompanyOwner = (boolean)($currentUser['0']['User']['role'] != 'company');
        if( $notCompanyOwner  ) {

            return true;
        }

        $companyState = (boolean)$currentUser['0']['Company']['is_enabled'];
        //writes in Auth.User array company's state
        $this->Session->write( 'Auth.User.is_enabled', $companyState);

        return $companyState;
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
