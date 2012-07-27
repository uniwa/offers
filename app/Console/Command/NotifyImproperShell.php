<?php

App::uses('CakeEmail', 'Network/Email');

class NotifyImproperShell extends AppShell {

    public $uses = array('Offer', 'Coupon');

    public function main() {

        $epo = $this->getEmailsPerOffer(strtotime('-3 hours'));

        $this->sendEmails($epo);
    }

    private function getEmailsPerOffer($since) {


        /* rid ourselves of unnecessary joins */
        $this->Offer->unbindModel(array('hasMany' => array('Coupon',
                                                           'Image',
                                                           'WorkHour')));

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
                                       'User.id = Student.user_id',
                                   ))),
                         'fields' => array('DISTINCT User.email',
                                           'Offer.title',
                                           'Offer.explanation'));

        $result = $this->Offer->find('all', $options);

        return $result;
    }
}
