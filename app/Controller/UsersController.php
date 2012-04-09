<?php

class UsersController extends AppController {

    public $uses = array('User', 'Image', 'Day', 'WorkHour', 'Municipality', 'Company');
    function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('register');

        // In case user tries to register when logged in
        if ($this->Auth->user() && $this->request['action'] == 'register') {
            throw new ForbiddenException('Δεν επιτρέπεται η πρόσβαση');
        }
    }

    function login() {
        if ($this->request->is('post')) {
            $userlogin = $this->Auth->login();
            if ($userlogin) {
                $user = $this->Auth->user();
                $username = $user['username'];
                $role = $user['role'];
                $currentUser = $this->User->find('first',
                    array('conditions' => array('username' => $username)));

                if ($role == ROLE_COMPANY && !$currentUser['Company']['is_enabled']) {
                    $this->Auth->logout();
                    $this->Session->setFlash(
                        __("Ο λογαριασμός σας δεν έχει ενεργοποιηθεί"),
                        'default',
                        array('class' => Flash::Error));
                    return;
                }

                // save last login field
                $this->User->id = $this->Auth->user('id');
                $this->User->saveField('last_login', date(DATE_ATOM), false);

                // redirect to profile on 1st login
                // admins always go to the default screen
                if ( $currentUser['User']['last_login'] == null ) {
                    if ($role === ROLE_COMPANY) {
                        $this->redirect(array('controller' => 'companies', 'action' => 'view'));
                    }
                    if ($role === ROLE_STUDENT) {
                        $this->redirect(array('controller' => 'students', 'action' => 'view'));
                    }
                }

                return $this->redirect($this->Auth->redirect());
            } else {
                $this->Session->setFlash(
                    __("Δώστε έγκυρο όνομα και κωδικό χρήστη"),
                    'default',
                    array('class' => Flash::Error));
            }
        }
    }


    function logout() {

        $this->redirect( $this->Auth->logout() );

    }

    function register() {

        if( !empty( $this->request->data ) ) {
            //is_enabled and is_banned is by default false
            //set registered User's role
            $this->request->data['User']['role'] =  ROLE_COMPANY;

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

    //Terms of use action
    public function terms() {
        $data = $this->request->data;
        if (!empty($data)) {
            $accept = $data['User']['accept'];
            if ($accept == 1) {
                $this->User->id = $this->Auth->user('id');
                $save = $this->User->saveField('terms_accepted', true, false);

                // reload user info after the update
                $this->Session->write('Auth',
                    $this->User->read(null, $this->Auth->user('id')));
                $this->Session->setFlash(
                    __('Έχετε αποδεχτεί τους όρους χρήσης'),
                    'default',
                    array( 'class'=>Flash::Success));
                $this->redirect(array('controller' => 'offers', 'action' => 'index'));
            } else {
                $this->Session->setFlash(
                    __('Δεν έχετε αποδεχτεί τους όρους χρήσης'),
                    'default',
                    array('class'=>Flash::Error));
                $this->Auth->logout();
                $this->redirect(array('controller' => 'offers', 'action' => 'index'));
            }
        } else {
            $cur_user = $this->Auth->user();
            $terms_accepted = $cur_user['terms_accepted'];
            $this->set('terms_accepted', $terms_accepted);
        }
    }
}
