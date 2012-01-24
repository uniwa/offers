<?php

class UsersController extends AppController {

    public $uses = array('User', 'Image');

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
        $currentUser = $this->User->find( 'all',
            array( 'conditions' => array( 'username' => $username ) )
        );


        //checks if current user not found
        //or checks if user is not company owner
        //and returns true to continue in login method
        if( empty( $currentUser ) || $currentUser['0']['User']['role'] != 'company'  ) {

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

        if( !empty( $this->request->data ) ) {

            //is_enabled and is_banned is by default false
            //set registered User's role
            $this->request->data['User']['role'] =  'company';
            //Use this to avoid valdation errors
            unset($this->User->Company->validate['user_id']);
            if (is_uploaded_file($this->data['Company']['image']['tmp_name'])) {
                $file = fread(fopen($this->data['Company']['image']['tmp_name'], 'r'),
                                    $this->data['Company']['image']['size']);

                $photo = array();
                $photo['Image'] = $this->data['Company']['image'];
                $photo['Image']['data'] = base64_encode($file);

                if ($this->Image->save($photo))
                    $this->request->data['Company']['image_id'] = $this->Image->id;
                else
                    $this->request->data['Company']['image_id'] = null;
            } else {
                $this->request->data['Company']['image_id'] = null;
            }

            if( $this->User->saveAssociated($this->request->data) ){

                $this->Session->setFlash(__('Η εγγραφή ολοκληρώθηκε') );
                $this->redirect(array('action' => 'index'));
            }

            $this->Session->setFlash(__('Η εγγραφή δεν ολοκληρώθηκε'));
        }

        App::uses( 'Day', 'app/Model');
        App::uses( 'Hour', 'app/Model');

        $day = new Day();
        $hour = new Hour();

        $this->set( "hours", $hour->find('list') );
        $this->set( "days", $day->find('list') );
    }
}
