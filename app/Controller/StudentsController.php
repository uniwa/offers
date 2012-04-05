<?php

class StudentsController extends AppController {

    public $name = 'Students';
    public $helpers = array('Html');
    public $uses = array('User', 'Student');

    function view($id = null) {
        $options = array(
            'conditions' => array('Student.user_id' => $this->Auth->user('id')),
            'recursive' => 0
        );

        $user = $this->Student->find('first', $options);

        if (empty($user))
            throw new NotFoundException('Το συγκεκριμένο profile χρήστη δεν
                                        βρέθηκε.');

        $this->set('user', $user);
    }
}

