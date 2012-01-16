<?php

class OffersController extends AppController {

    public $name = 'Offers';
    public $helpers = array('Form');

    public function add() {
        $this->set('offerTypes', $this->Offer->OfferType->find('list'));
        $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));
    }
}
