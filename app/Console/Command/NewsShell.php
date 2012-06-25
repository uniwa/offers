<?php

App::uses('CakeEmail', 'Network/Email');

class NewsShell extends AppShell {
    public $uses = array('Student', 'Offer', 'Company');
    public $helpers = array('Html');

    private $default_published = 'Δημοσιεύθηκε στις d/m/Y, H:i';

    public function main() {

        // default behaviour is to send emails for offers activated the previous
        // day
        $since = date('Y-m-d', strtotime('-1 day'));
        $until = date('Y-m-d');

        $this->within($since, $until, 'Ώρα δημοσίευσης H:i');
    }

    // Send a news email for offers with an activation date between $since
    // (inclusive) and $until (exclusive).
    //
    // This is the default behaviour of this Shell.
    //
    // @param $since date
    // @param $until date
    // @published_format A string to be passed into strtotime() for the
    //      activation date of each offer.
    public function within($since, $until, $published_format = null) {

        $this->Student->recursive = 0;
        $students = $this->Student->findAllByReceiveEmail('true',
                                                          array('User.email'));

        if (! empty($students)) {

            $offers = $this->get_offers($since, $until);
            $this->Offer->minify_desc($offers, 160);

            if (! empty($offers)) {
                // one second is subtracted because the exact value of $until is
                // excluded
                $formatted = date('d/m/Y', strtotime($until) - 1);

                $this->email_news(
                        $students,
                        $offers,
                        "Νέες προσφορές ($formatted)",
                        $published_format);
            }

        }
    }

    // @param $since Offers activated after this date will be included
    // @param $until Offers activated before but NOT on this date will be
    //      included
    private function get_offers($since, $until) {
        // find offers that were published just yesterday
        $this->Offer->recursive = 2;

        // if you don't like unbind(), just consider this:
        //      Did you know that otherwise, 4 (LEFT JOIN ) queries would be run
        //      when only one is needed?
        $this->Offer->unbindModel(array('hasMany' => array('Coupon',
                                                           'WorkHour',
                                                           'Image')));

        $conditions = array('Offer.started >=' => $since,
                            'Offer.started <' => $until,
                            'Offer.offer_state_id' => STATE_ACTIVE);

        $fields = array('Offer.id',
                        'Offer.title',
                        'Offer.offer_type_id',
                        'Offer.started',
                        'Offer.description',
                        'OfferCategory.name',
                        'Company.name');

        $options = array('conditions' => $conditions,
                         'fields' => $fields,
                         'order' => 'Offer.started');

        return $offers = $this->Offer->find('all', $options);

    }

    private function email_news($users, $offers, $subject = 'Νέες προσφορές',
            $published_format = null) {

        if (empty($published_format))
            $published_format = $this->default_published;

        // available offer types
        $offer_types = array(
            TYPE_HAPPYHOUR => 'Περιοδική προσφορά',
            TYPE_COUPONS => 'Προσφορά κουπονιών',
            TYPE_LIMITED => 'Απλή προσφορά');

        $email = new CakeEmail('default');
        $email = $email
            ->subject($subject)
            ->template('new_offers', 'default')
            ->emailFormat('both')
            ->viewVars(array(
                // we need to know the *actual* base url
                'app_url' => Configure::read('Constants.APP_URL'),
                'offers' => $offers,
                'offer_types' => $offer_types,
                // format for the activation date of each offer
                'published_format' => $published_format,
            ));

        foreach ($users as $user) {

            $email = $email->to($user['User']['email']);

            try {
                $email->send();
            } catch (Exception $e) {
                //do what with it?
            }
        }
    }

}
