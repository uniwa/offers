<?php

class OffertypesController extends  AppController {

    public $name = 'Offertypes';
    public $uses = array('OfferType');
    public $helpers = array('Html', 'Form');


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


    public function add () {
        if (!empty($this->data)) {
            if ($this->OfferType->save($this->data)) {
                $this->Session->setFlash('Η αποθήκευση ήταν επιτυχής.');
                $this->redirect(array('controller' => 'Offertypes'));
            } else {
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.');
            }
        }
    }
}
