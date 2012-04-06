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

                if ($role != ROLE_ADMIN) {
                    //writes student's or company's related id inside Session
                    $this->Session->write('Auth.User.role_id',
                        (empty($currentUser['Company']['id']))?
                            $currentUser['Student']['id']:
                            $currentUser['Company']['id']);
                }   

                if ($role == ROLE_COMPANY && !$currentUser['Company']['is_enabled']) {
                    $this->Auth->logout();
                    $this->Session->setFlash(
                        __("Ο λογαριασμός σας δεν έχει ενεργοποιηθεί"),
                        'default',
                        array('class' => Flash::Error));
                    return;
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
}
