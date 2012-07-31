<?php

// Example, running:
//
// ./Console/cake company_coupons since '24 hours'
//
// will send an e-mail listing all coupons of each offer that has been
// terminated within the last '24 hours' to their respective company owner. This
// is the default behaviour, i.e. running this, instead:
// ./Console/cake company_coupons
// will produce the same result.

// Any coupon that had been reinserted, i.e. 'deleted' while the offer was still
// active.will not be included in the listing.
App::uses('CakeEmail', 'Network/Email');

class CompanyCouponsShell extends AppShell {

    public $uses = array('Offer', 'Coupon');

    public function main() {
        $this->run(strtotime('-24 hours'));
    }

    // args[0] : date idenntifier; anythting that can be passed into date() 's
    // second parameter
    public function since() {
        $since = strtotime('-' . $this->args[0]);
        $this->run($since);
    }

    // $since : a Unix timestamp
    private function run($since) {
        $result = $this->getCoupons(date('Y-m-d H:i:s', $since));
        $this->sendEmails($result);
    }

    // $since : a properly formatted date string
    private function getCoupons($since) {

        $this->Offer->recursive = -1;
        $options = array('conditions' => array(
                             'Offer.offer_type_id' => TYPE_COUPONS,
                             'Offer.is_spam' => false,
                             'Offer.offer_state_id' => Configure::read('Constants.STATE_INACTIVE'),
/*                             'Offer.modified >=' => $since,*/
                             'Coupon.reinserted' => false),
                         'joins' => array(
                             array('table' => 'coupons',
                                   'alias' => 'Coupon',
                                    // use FULL join, to ignore coupon offer
                                    // with no reserved coupons
                                    //'type' => '',
                                   'conditions' => array(
                                       'Coupon.offer_id = Offer.id',
                                   )),
                             array('table' => 'companies',
                                   'alias' => 'Company',
                                   'type' => 'LEFT',
                                   'conditions' => array(
                                       'Offer.company_id = Company.id',
                                   )),
                             array('table' => 'users',
                                   'alias' => 'User',
                                   'type' => 'LEFT',
                                   'conditions' => array(
                                       'Company.user_id = User.id',
                                   ))),
                         'fields' => array('Offer.id',
                                           'Offer.title',
                                           'Coupon.serial_number',
                                           'User.email'),
                         'order' => array('Offer.id ASC'));

        $result = $this->Offer->find('all', $options);

        // alter results so that they can be iterated more easily
        $result = Set::combine($result,
                               '{n}.Coupon.serial_number',
                               '{n}.User.email',
                               '{n}.Offer.title');

        return $result;
    }

    private function sendEmails($records) {
print_r($records);
        $email = new CakeEmail('default');
        // set parameters that are the same for all emails to be sent
        $email = $email
            ->template('company_coupons', 'default')
            ->emailFormat('both');

        foreach ($records as $offer_title => $coupons) {
            $email
                ->to(reset($coupons))
                ->subject(__("Κουπόνια προσφοράς «{$offer_title}»"))
                ->viewVars(array(
                    'offer_title' => $offer_title,
                    'coupons' => $coupons));

            try {
                $email->send();
            } catch(Exception $e) {}
        }
    }

}
