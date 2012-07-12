<?php

class Coupon extends AppModel {

    public $name = 'Coupon';
    public $belongsTo = array('Student',
                              'Offer' => array(
                                    'counterCache' => true
                                ));

    public function max_coupons_reached($offer_id, $student_id) {
        // get max allowed coupons per student
        $offer_coupons = $this->Offer->field('max_per_student',
                                             array('id' => $offer_id));

        if ($offer_coupons == BIND_UNLIMITED)
            return false;

        // get number of coupons the student already has
        $conditions = array('student_id' => $student_id,
                            'offer_id' => $offer_id);

        $student_coupons = $this->find('count',
                                       array('conditions' => $conditions));


        return $student_coupons >= $offer_coupons;
    }

    public function student_coupons($offer_id, $student_id) {
        // get coupons booked by specific student
        $conditions = array('student_id' => $student_id,
                            'offer_id' => $offer_id);

        $student_coupons = $this->find('all', array(
            'conditions' => $conditions,
            'recursive' => -1));

        return $student_coupons;
    }

    public function is_owned_by($coupon_id, $student_id) {
        $conditions = array('Coupon.id' => $coupon_id);
        // notice:
        //   $this->field is inconsistent with other methods
        //   so array('conditions' => $conditions) will not work
        $coupon_student = $this->field('student_id', $conditions);
        return $coupon_student === $student_id;
    }

    public function is_offered_by($coupon_id, $company_id) {
        $this->Behaviors->attach('Containable');
        $this->contain(array('Offer.company_id'));

        $options['conditions'] = array('Offer.company_id' => $company_id,
                                       'Coupon.id' => $coupon_id);

        $result = $this->find('first', $options);
        return $result['Offer']['company_id'] === $company_id;
    }
}
