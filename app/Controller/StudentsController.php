<?php

class StudentsController extends AppController {

    public $name = 'Students';
    public $helpers = array('Html', 'Time');
    public $uses = array('User', 'Student', 'Coupon');

    public function beforeFilter() {
        if (! $this->is_authorized($this->Auth->user()))
            throw new ForbiddenException();

        parent::beforeFilter();
    }

    public function view($id = null) {
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
                                 'email' => $user['User']['email'],
                                 'receive_email' => $user['Student']['receive_email']));

        // get all student coupons
        $cond = array(
            'Coupon.student_id' => $this->Session->read('Auth.Student.id')
        );
        $order = array('Coupon.created DESC');
        $this->Coupon->recursive = 0;

        // we also need company name
        $this->Coupon->Behaviors->attach('Containable');
        $this->Coupon->contain('Offer.Company.name');

        $coupons = $this->Coupon->find('all', array(
            'conditions' => $cond,
            'order' => $order)
        );
        $this->set('coupons', $coupons);
    }

    public function subscribe() {
        $this->offer_mailing($this->Session->read('Auth.Student.id'), true);
    }
    public function unsubscribe() {
        $this->offer_mailing($this->Session->read('Auth.Student.id'), false);
    }

    private function offer_mailing($id, $subscribe = true) {
        $this->Student->id = $id;
        if ($this->Student->saveField('receive_email', $subscribe, false)) {

            if ($subscribe) {
                $message = 'Θα σας αποστέλλεται μήνυμα μία φορά την ημέρα με '.
                           'τις νέες προσφορές';
            } else {
                $message = 'Δεν θα λαμβάνετε πλέον μήνυμα με νέες προσφορές';
            }
            $class = Flash::Info;
        } else {
            $message = 'Προέκυψε κάποιο σφάλμα';
            $class = Flash::Error;
        }
        $this->Session->setFlash($message, 'default', array('class' => $class));
        $this->redirect($this->request->referer());
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

