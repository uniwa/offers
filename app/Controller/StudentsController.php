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

            // admins query students using student ids
            $options['conditions'] = array('Student.id' => $id);

        } else {
            // users query their own profile using their user id
            $options['conditions'] = array('Student.user_id' => $this->Auth->user('id'));
        }
        $options['recursive'] = 0;

        // get student profile and user info
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

    public function is_authorized($user) {
        // only students can see profiles
        if ($user['role'] === ROLE_STUDENT) {
            return true;
        }

        // Admin sees all, deny for everyone else
        return parent::is_authorized($user);
    }
}

