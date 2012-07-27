<?php

App::uses('CakeEmail', 'Network/Email');

class NotifyImproperShell extends AppShell {

    public $uses = array('Offer', 'Coupon');

    public function main() {

        $epo = $this->getEmailsPerOffer(strtotime('-3 hours'));

        $this->sendEmails($epo);
    }

    private function getEmailsPerOffer($since) {

        $this->Offer->Behaviors->attach('Containable');
        $this->Offer->contain(array('User.email'));

        $options = array('conditions' => array(
                             'Offer.offer_type_id' => TYPE_COUPONS,
                             'Offer.is_spam' => true,
/*                             'Offer.modified >=' => $since*/),
                         'joins' => array(
                             array('table' => 'coupons',
                                   'alias' => 'Coupon',
                                    // use FULL join, to ignore coupon offer
                                    // with no reserved coupons
                                    //'type' => '',
                                   'conditions' => array(
                                       'Coupon.offer_id = Offer.id',
                                   )),
                             array('table' => 'students',
                                   'alias' => 'Student',
                                   'type' => 'LEFT',
                                   'conditions' => array(
                                       'Coupon.student_id = Student.id',
                                   )),
                             array('table' => 'users',
                                   'alias' => 'User',
                                   'type' => 'LEFT',
                                   'conditions' => array(
                                       'User.id = Student.id',
                                   ))),
                         'fields' => array('DISTINCT User.email, Offer.title'));

        $result = $this->Offer->find('all', $options);

        return $result;
    }
}
