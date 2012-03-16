<?php

class UsersController extends AppController {

    public $uses = array('User', 'Image', 'Day', 'WorkHour', 'Municipality', 'Company');
    function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow('register');

        //in case user try to get  register when is logged in
        if( $this->Auth->user() && $this->request['action'] == 'register') {

                throw new ForbiddenException('Δεν επιτρέπεται η πρόσβαση');
        }

    }

    function login() {

        if( $this->request->is( 'post' ) ) {

            if( $this->isCompanyEnabled( $this->request->data ) ) {

                if( $this->Auth->login() ) {

                    //get user or company id and set it in Sesion Auth.User 
                    //array as role_id
                    $user = $this->User->find( 'first', 
                        array ( 'conditions' => array( 'User.id'=> $this->Auth->user('id') ) ) );
                    $this->Session->write( 'Auth.User.role_id', 
                        (empty($user['Company']['id']))?$user['Student']['id']:$user['Company']['id']);

                    return $this->redirect( $this->Auth->redirect() );
                } else {

                    $this->Session->setFlash(__("Δώστε έγκυρο όνομα και κωδικό χρήστη"),
                                             'default',
                                             array('class' => Flash::Error));
                }
            } else {

                $this->Session->setFlash(__("Ο λογαριασμός σας δεν έχει ενεργοποιηθεί"),
                                         'default',
                                         array('class' => Flash::Error));
            }
        }
    }

    //This function returns company state result( is_enabled )
    //plus returns true if user is not company owner or is not exist
    private function isCompanyEnabled( $data ) {

        $username = $data['User']['username'];
        $currentUser = $this->User->find( 'first',
            array( 'conditions' => array( 'username' => $username ) )
        );


        //1.checks if current user not found
        //2.or checks if user is not company owner
        //and returns true to continue in login method
        if( empty( $currentUser ) || $currentUser['User']['role'] != 'company'  ) {

            return true;
        }

        return $currentUser['Company']['is_enabled'];

    }

    function logout() {

        $this->redirect( $this->Auth->logout() );

    }

    function register() {

        if( !empty( $this->request->data ) ) {
            //is_enabled and is_banned is by default false
            //set registered User's role
            $this->request->data['User']['role'] =  'company';

            if($this->User->saveAssociated($this->request->data)) {
                $this->Session->setFlash(__('Η εγγραφή ολοκληρώθηκε'),
                                         'default',
                                         array('class' => Flash::Success));
                $this->redirect(array('controller'=>'Offers', 'action' => 'index'));
            } else
                $this->Session->setFlash(__('Η εγγραφή δεν ολοκληρώθηκε'),
                                         'default',
                                         array('class' => Flash::Error));
        }
    }
}
