<?php

class Offer extends AppModel {

    public $name = 'Offer';
    public $belongsTo = array('Company', 'OfferType', 'OfferCategory', 'OfferState');
    public $hasMany = array('Coupon', 'Image', 'WorkHour');

    // This function updates the state of all offers based on their autostart
    // and autoend values.
    // It should be invoked BEFORE any operation that requires up-to-date offer
    // state.
    //
    // @param $company_id limits update operations only to offers that belong to
    // the specified company id
    public function update_state($company_id = NULL) {
        // date by which to compare autostart and autoend dates
        $now = date('Y-m-d H:i:s');

        $this->activate_offers($company_id, $now);
        $this->deactivate_offers($company_id, $now);
    }

    private function activate_offers($company_id = NULL, $date) {
        // happy-hour offers do not automatically activate

        // coupon offers activate if current date is later than an offer's
        // autostart date and the available coupons are not yet depleted

        // (time-)limited offers activate if current date is between autostart
        // and autoend dates

            // what about `is_spam' ?
            // is it definite that such offers have already been set to
            // inactive?

        $conditions = array(
            // we bear in mind not to activate any offers that have been
            // manually deactivated (ie, we activate only Draft offers)
            'Offer.offer_state_id' => STATE_DRAFT,
            'OR' => array(
                // conditions to activate a Coupons-type offer
                array(
                    'Offer.offer_type_id' => TYPE_COUPONS,
                    'Offer.autostart <=' => $date,
                    'Offer.coupon_count < Offer.total_quantity'
                ),

                // conditions to activate a (Time-)Limited-type offer
                array(
                    'Offer.offer_type_id' => TYPE_LIMITED,
                    'Offer.autostart <=' => $date,
                    'Offer.autoend >' => $date
                )
            )
        );

        if(!empty($company_id)) {
            array_push($conditions, array('Offer.company_id' => $company_id));
        }

        $this->updateAll(
            array(
                'Offer.offer_state_id' => STATE_ACTIVE,
                'Offer.started' => 'Offer.autostart'
            ),
            $conditions
        );
    }

    private function deactivate_offers($company_id = NULL, $date) {
        // happy-hour offers do not automatically deactivate

        // coupon offers deactivate if no more coupons are available

        // (time-)limited offers deactivate if current date exceeds autoend

        $conditions = array(
            // we bear in mind to deactivate offers that are only currently
            // active, effectively avoiding writing to already inactive ones
            'Offer.offer_state_id' => STATE_ACTIVE,
            'OR' => array(
                // conditions to deactivate a coupons-type offer
                array(
                    'Offer.offer_type_id' => TYPE_COUPONS,
                    'Offer.coupon_count >= Offer.total_quantity'
                ),

                // conditions to deactivate a (time-)limited-type offer
                array(
                    'Offer.offer_type_id' => TYPE_LIMITED,
                    'Offer.autoend <=' => $date
                )
            )
        );
        if(!empty($company_id)) {
            array_push($conditions, array( 'Offer.company_id' => $company_id ));
        }

        $this->updateAll(
            array(
                'Offer.offer_state_id' => STATE_INACTIVE,
                'Offer.ended' => 'Offer.autoend'
            ),
            $conditions
        );
    }

}
