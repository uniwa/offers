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

        $this->set('user', $user);
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

