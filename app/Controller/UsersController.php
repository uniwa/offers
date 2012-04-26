<?php

class UsersController extends AppController {

    public $uses = array('User', 'Image', 'Day',
                         'WorkHour', 'Municipality', 'Company', 'Student');

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
                if ($this->Auth->user('role') == ROLE_COMPANY) {
                    // check if company is enabled
                    $options['conditions'] = array(
                        'User.id' => $this->Auth->user('id')
                    );
                    $options['fields'] = array('Company.is_enabled');
                    $options['recusive'] = 0;

                    $enabled = $this->User->find('first', $options);
                    $enabled = Set::extract($enabled, 'Company.is_enabled');

                    if (! $enabled) {
                        $this->Auth->logout();
                        $this->Session->setFlash(
                            __("Ο λογαριασμός σας δεν έχει ενεργοποιηθεί"),
                            'default',
                            array('class' => Flash::Error));
                        return;
                    }
                }

                // save last login field
                $this->User->id = $this->Auth->user('id');
                $this->User->saveField('last_login', date(DATE_ATOM), false);

                // Save student and company profile id in session
                // as they are widely used thoughout the application
                //  for students:
                //      Auth.Student.id
                //  for companies:
                //      Auth.Company.id
                //
                //  Retrieving this data from session (controller/views):
                //      $this->Session->read('Auth.Company.id');
                //      $this->Session->read('Auth.Student.id');
                //
                if ($this->Auth->user('role') === ROLE_COMPANY) {
                    $company_id = $this->Company->field('id',
                        array('user_id' => $this->Auth->user('id')));
                    $this->Session->write('Auth.Company.id', $company_id);

                } elseif ($this->Auth->user('role') === ROLE_STUDENT) {
                    $student_id = $this->Student->field('id',
                        array('user_id' => $this->Auth->user('id')));
                    $this->Session->write('Auth.Student.id', $student_id);
                }

                // redirect to profile on 1st login
                // admins always go to the default screen
                if ( $this->Auth->user('last_login') == null ) {
                    if ($this->Auth->user('role') === ROLE_COMPANY) {
                        $this->redirect(array(
                            'controller' => 'companies', 'action' => 'view'
                        ));
                    }
                    if ($this->Auth->user('role') === ROLE_STUDENT) {
                        $this->redirect(array(
                            'controller' => 'students', 'action' => 'view'
                        ));
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
                $this->redirect(array(
                    'controller'=>'Offers', 'action' => 'index'
                ));
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
                $this->redirect(array(
                    'controller' => 'offers', 'action' => 'index'));
            } else {
                $this->Session->setFlash(
                    __('Δεν έχετε αποδεχτεί τους όρους χρήσης'),
                    'default',
                    array('class'=>Flash::Error));
                $this->Auth->logout();
                $this->redirect(array(
                    'controller' => 'offers', 'action' => 'index'
                ));
            }
        } else {
            $this->set('terms_accepted', $this->Auth->user('terms_accepted'));
        }
    }

    // Frequently asked questions
    public function faq() {
    }

}
