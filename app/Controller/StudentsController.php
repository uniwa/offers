<?php

class StudentsController extends AppController {

    public $name = 'Students';
    public $helpers = array('Html');
    public $uses = array('User', 'Student');

    public function view($id = null) {
        if (! $this->is_authorized($this->Auth->user()) )
            throw new ForbiddenException();

        // admin does not have a profile, must give a profile $id
        // to view other profiles
        if ( $this->Auth->User('role') === ROLE_ADMIN) {
            if ($id == null) {
                throw new NotFoundException('Το συγκεκριμένο profile χρήστη δεν
                                            βρέθηκε.');
            }
        } else {
            $id = $this->Auth->user('id');
        }

        // get student profile and user info
        $options = array(
            'conditions' => array('Student.user_id' => $id),
            'recursive' => 0
        );

        $user = $this->Student->find('first', $options);

        if (empty($user))
            throw new NotFoundException('Το συγκεκριμένο profile χρήστη δεν
                                        βρέθηκε.');

        $this->set('user', array('firstname' => $user['Student']['firstname'],
                                 'lastname' => $user['Student']['lastname'],
                                 'username' => $user['User']['username'],
                                 'email' => $user['User']['email'])
        );
    }

    //terms of use action
    public function terms() {
        $data = $this->request->data;
        if (!empty($data)) {
            $accept = $data['User']['accept'];
            if ($accept == 1) {
                $this->User->id = $this->Auth->user('id');
                $this->User->saveField('terms_accepted', true);

                // reload user info after the update
                $this->Session->write('Auth',
                    $this->User->read(null, $this->Auth->user('id')));
                $this->Session->setFlash(
                    __('Έχετε αποδεχτεί τους όρους χρήσης'),
                    'default',
                    array( 'class'=>Flash::Success));
                $this->redirect(array('controller'=>'Offers', 'action' => 'index'));
            } else {
                $this->Session->setFlash(
                    __('Δεν έχετε αποδεχτεί τους όρους χρήσης'),
                    'default',
                    array('class'=>Flash::Error));
                $this->Auth->logout();
                $this->redirect(array('controller'=>'Offers', 'action' => 'index'));
            }
        } else {
            $cur_user = $this->Auth->user();
            $terms_accepted = $cur_user['terms_accepted'];
            $this->set('terms_accepted', $terms_accepted);
        }
    }

    public function is_authorized($user) {
        // only students can see profiles
        if ($user['role'] === ROLE_STUDENT) {
            return true;
        }

        // Admin sees all, deny for everyone else
        return parent::is_authorized($user);
    }
}

