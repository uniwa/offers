<?php

// Example, running:
//
// ./Console/cake notify_improper since '3 hours'
//
// will take into account any coupon offers that have been flagged as improper
// within the last 3 hour. This is the default behaviour, i.e. if this is run, instead:
// ./Console/cake notify_improper

App::uses('CakeEmail', 'Network/Email');

class NotifyImproperShell extends AppShell {

    public $uses = array('Offer', 'Coupon');

    public function main() {
        $this->run(strtotime('-3 hours'));
    }

    // args[0] : date idenntifier; anythting that can be passed into date() 's
    // second parameter
    public function since() {
        $since = strtotime('-' . $this->args[0]);
        $this->run($since);
    }

    // $since : a Unix timestamp
    private function run($since) {
        $epo = $this->getEmailsPerOffer(date('Y-m-d H:i:s', $since));
        $this->sendEmails($epo);
    }

    // $since : a properly formatted date string
    private function getEmailsPerOffer($since) {

        /* rid ourselves of unnecessary joins */
        $this->Offer->unbindModel(array('hasMany' => array('Coupon',
                                                           'Image',
                                                           'WorkHour')));

        $options = array('conditions' => array(
                             'Offer.offer_type_id' => TYPE_COUPONS,
                             'Offer.is_spam' => true,
                             'Offer.modified >=' => $since),
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
                                           'Offer.id',
                                           'Offer.title',
                                           'Offer.explanation'),
                          'order' => array('Offer.id ASC'));

        $result = $this->Offer->find('all', $options);

        return $result;
    }

    private function sendEmails($epo) {
        $offer_id = 0;
        $app_url = Configure::read('Constants.APP_URL');


        $email = new CakeEmail('default');
        // set parameters that are the same for all emails to be sent
        $email = $email
            ->subject(__("Ειδοποίηση ανάρμοστης προσφοράς"))
            ->template('improper_notify', 'default')
            ->emailFormat('both')
            ->viewVars(array('for_student' => true));

        foreach ($epo as $record) {
            // set params that change with each new offer
            if ($offer_id != $record['Offer']['id']) {
                $offer_id = $record['Offer']['id'];

                $email->viewVars(array(
                    'url' => $app_url.'/offers/view/'.$offer_id,
                    'title' => $record['Offer']['title'],
                    'explanation' => $record['Offer']['explanation']));
            }

            $email->to($record['User']['email']);

            try {
                $email->send();
            } catch(Exception $e) {}
        }
    }
}
