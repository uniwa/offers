<?php

class CouponsController extends AppController {

    public $name = 'coupons';
    public $uses = array('Coupon', 'Offer');

    public function add () {
        if ($this->Auth->User('role') !== ROLE_STUDENT)
            throw new ForbiddenException();

        if (empty($this->request->data))
            throw new BadRequestException();

        if ($this->Auth->User('id') !== $this->request->data['Coupon']['student_id'])
            throw new BadRequestException();

        // throw an exception in case the student can get only one coupon
        //$options['conditions'] = array('Coupon.student_id' => $this->request->data['Coupon']['student_id'],
        //                               'Coupon.offer_id' => $this->request->data['Coupon']['offer_id']);
        //$options['recursive'] = -1;
        //$coupon = $this->Coupon->find('first', $options);
        //if (!empty($coupon))
        //    throw new BadRequestException('Έχετε ήδη ένα κουπόνι για αυτή την προσφορά');

        // Create a UUID
        // TODO check for duplicate ?
        $uuid = $this->generate_uuid();
        $this->request->data['Coupon']['serial_number'] = $uuid;
        $this->request->data['Coupon']['is_used'] = 0;

        if ($this->Coupon->save($this->request->data))
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
}
