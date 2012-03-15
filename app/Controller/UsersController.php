<?php

class UsersController extends AppController {

    public $uses = array('User', 'Image', 'Day', 'WorkHour', 'Municipality', 'Company');
    function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow('register');
        $this->Auth->allow('days' );

        //in case user try to get  register when is logged in
        if( $this->Auth->user() && $this->request['action'] == 'register') {

                throw new ForbiddenException('Δεν επιτρέπεται η πρόσβαση');
        }

    }

    function login() {

        if( $this->request->is( 'post' ) ) {

            if( $this->isCompanyEnabled( $this->request->data ) ) {

                if( $this->Auth->login() ) {

                    return $this->redirect( $this->Auth->redirect() );
                } else {

                    $this->Session->setFlash(__("Δώστε έγκυρο όνομα και κωδικό χρήστη"), 'default', array(), 'auth' );
                }
            } else {

                $this->Session->setFlash(__("Ο λογαριασμός σας δεν έχει ενεργοποιηθεί"), 'default', array(), 'auth' );
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
                $this->Session->setFlash(__('Η εγγραφή ολοκληρώθηκε') );
                $this->redirect(array('controller'=>'Offers', 'action' => 'index'));
            } else
                $this->Session->setFlash(__('Η εγγραφή δεν ολοκληρώθηκε'), 'default', array('class'=>$this->ERROR));
        }
    }

    function days(){

        $this->set( "days", $this->Day->find('list') );
        $this->set("_serialize", "days");
    }

    //sets Company id from saved company
    private function setCompanyId( $c_id, $workHour ) {

        if( empty( $c_id ) ){
            return null;
        }
        //creates the work hour format, compatible with mysql
        foreach( $workHour as &$wh ){

            $wh['company_id'] = $c_id;
        }

        return $workHour;
    }
}
