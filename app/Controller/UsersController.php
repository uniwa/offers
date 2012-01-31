<?php

class UsersController extends AppController {

    public $uses = array('User', 'Image', 'Day', 'WorkingHour', 'Image');

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
// pr($this->data);die();
            $dataSource = $this->User->getDataSource();
            //is_enabled and is_banned is by default false
            //set registered User's role
            $this->request->data['User']['role'] =  'company';
            //Use this to avoid valdation errors
            unset($this->User->Company->validate['user_id']);

            $dataSource->begin();

            // avatar stuff
            $photo = array();
            if (is_uploaded_file($this->data['Company']['image']['tmp_name'])) {
                if ($this->isImage($this->data['Company']['image']['type'])) {

                    $file = fread(fopen($this->data['Company']['image']['tmp_name'], 'r'),
                                  $this->data['Company']['image']['size']);
                    $photo['Image'] = $this->data['Company']['image'];
                    $photo['Image']['data'] = base64_encode($file);
                    //TODO change the hardcoded image category
                    $photo['Image']['image_category_id'] = 3;
                    if ($this->Image->save($photo)) {
                        $this->request->data['Company']['image_id'] = $this->Image->id;
                    } else {
                        $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα στην φωτογραφία.');
                        $dataSource->rollback();
                        return;
                    }
                } else {
                    $this->Session->setFlash('Μη αποδεκτός τύπος αρχείου εικόνας.');
                    return;
                }
            }
            // end of avatar stuff

            $workingHour = $this->request->data['WorkingHour'];
            unset( $this->request->data['WorkingHour']);

            $saved_user = $this->User->save( $this->request->data['User'] );
            $this->User->Company->set('user_id', $saved_user['User']['id']);
            $saved_comp = $this->User->Company->save( $this->request->data['Company']);
            $workingHour = $this->setCompanyId( $this->User->Company->id, $workingHour );

            $saved_hours = $this->WorkingHour->saveMany( $workingHour );

            if( $saved_user && $saved_comp && $saved_hours ){

                $dataSource->commit();
                $this->Session->setFlash(__('Η εγγραφή ολοκληρώθηκε') );
                $this->redirect(array('action' => 'index'));
            }
            $dataSource->rollback();
            $this->Session->setFlash(__('Η εγγραφή δεν ολοκληρώθηκε'));
        }


        $this->set( "days", $this->Day->find('list') );
    }

    //sets Company id from saved company
    private function setCompanyId( $c_id, $workingHour ) {

        if( empty( $c_id ) ){
            return null;
        }
        //creates the working hour format, compatible with mysql
        foreach( $workingHour as &$wh ){

            $wh['company_id'] = $c_id;
        }

        return $workingHour;
    }
}
