<?php

class UpdateStateShell extends AppShell {
    public $uses = array('Offer');

    public function main() {
        $now = date('Y/m/d H:i:s');
        $this->activate_offers($now);
        $this->deactivate_offers($now);
    }


    // Activates all coupon and time-limited offers that have an autostart date
    // before the date supplied, typically, now.
    //
    // @param $date
    private function activate_offers($date) {
        // happy-hour offers do not automatically activate

        // coupon and limited offers are activated if current date succeeds an
        // offer's autostart date

        // spam offers should already be set as inactive, so no need to check on
        // that as well

        $conditions = array(
            // we bear in mind not to activate any offers that have been
            // manually deactivated (ie, we activate only Draft offers)
            'Offer.offer_state_id' => STATE_DRAFT,
            'Offer.offer_type_id' => array(TYPE_COUPONS, TYPE_LIMITED),
            'Offer.autostart <=' => $date,
        );

        $this->Offer->updateAll(array('Offer.offer_state_id' => STATE_ACTIVE,
                                      'Offer.started' => 'Offer.autostart'),
                                $conditions);
    }

    // Deactivates time-limited offers that have an autoend date after the date
    // supplied, typically, now.
    //
    // @param $date
    private function deactivate_offers($date) {
        // happy-hour offers do not automatically deactivate

        // coupon offers deactivate if no more coupons are available, but this
        // should already be taken care of

        // (time-)limited offers deactivate if current date succeeds an offer's
        // autoend date

        $conditions = array(
            // we bear in mind to deactivate offers that are currently active,
            // effectively avoiding re-deactivating the inactive ones
            'Offer.offer_state_id' => STATE_ACTIVE,
            'Offer.offer_type_id' => TYPE_LIMITED,
            'Offer.autoend <=' => $date
        );

        $this->Offer->updateAll(array('Offer.offer_state_id' => STATE_INACTIVE,
                                      'Offer.ended' => 'Offer.autoend'),
                                $conditions);
    }

}
