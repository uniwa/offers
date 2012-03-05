<?php

class OffertypesController extends  AppController {

    public $name = 'Offertypes';
    public $uses = array('OfferType');
    public $helpers = array('Html');

    public function beforeFilter () {
        parent::beforeFilter();
        if ($this->Auth->User('role') !== 'admin') {
            throw new ForbiddenException();
        }
    }

    public function index () {

        $offerTypes = $this->OfferType->find('all');
        $this->set('results', $offerTypes);
    }
}
