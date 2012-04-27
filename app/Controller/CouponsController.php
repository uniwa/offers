<?php

class CouponsController extends AppController {

    public $name = 'coupons';
    public $uses = array('Coupon', 'Offer');

    public function beforeFilter() {
        if (! $this->is_authorized($this->Auth->user()))
            throw new ForbiddenException();

        parent::beforeFilter();
    }

    public function add ($id = null) {
        if ($this->Auth->User('role') !== ROLE_STUDENT)
            throw new ForbiddenException();

        if ($id === null)
            throw new BadRequestException();

        // TODO DEPRECATE
        // will use custom model function that checks for offer validity too
        // ----
        // check that the offer exists
        $this->Offer->recursive = -1;
        $conditions = array('Offer.id' => $id);
        $offer = $this->Offer->find('first',
                                    array('conditions' => $conditions));

        if (! $offer)
            throw new NotFoundException('Η προσφορά δεν βρέθηκε.');
        // --------------

        // don't read from session all the time
        $student_id = $this->Session->read('Auth.Student.id');

        // check if user is allowed to get the coupon due to maximum
        // coupon number acquired
        if ($this->Coupon->max_coupons_reached($id, $student_id)) {
            // TODO: make this a non 500 error
            throw new MaxCouponsException('Έχετε δεσμεύσει τον μέγιστο ' .
                'αριθμό κουπονιών για αυτήν την προσφορά.');
        }

        // create a unique id
        $coupon['Coupon']['serial_number'] = $this->generate_uuid();

        $coupon['Coupon']['is_used'] = 0;
        $coupon['Coupon']['student_id'] = $student_id;
        $coupon['Coupon']['offer_id'] = $id;

        if ($this->Coupon->save($coupon))
            $this->Session->setFlash('Το κουπόνι δεσμεύτηκε επιτυχώς',
                                     'default',
                                     array('class' => Flash::Success));
        else
            $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα',
                                     'default',
                                     array('class' => Flash::Error));

        $this->redirect($this->referer());
    }

    private function generate_uuid() {
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

        return $uuid;
    }

    public function is_authorized($user) {
        if ($user['is_banned'] == 0) {
            if ($this->action === 'add') {
                // only students can get coupons
                if ($user['role'] !== ROLE_STUDENT)
                    return false;
                return true;
            }
        }

        // admin can see banned users too
        return parent::is_authorized($user);
    }

}

class MaxCouponsException extends CakeException {};
