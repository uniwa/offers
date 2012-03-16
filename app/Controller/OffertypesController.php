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
                $this->Session->setFlash('Η αποθήκευση ήταν επιτυχής.',
                                         'default',
                                         array('class' => Flash::Success));
                $this->redirect(array('controller' => 'Offertypes'));
            } else {
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                                         'default',
                                         array('class' => Flash::Error));
            }
        }
    }


    public function edit ($id = null) {

        if ($id == null) throw new BadRequestException();

        if (empty($this->data)) {

            $options['conditions'] = array('OfferType.id' => $id);
            $type = $this->OfferType->find('first', $options);

            if (empty($type)) throw new NotFoundException();

            $this->request->data = $type;
        } else {

            if ($this->OfferType->save($this->data)) {
                $this->Session->setFlash('Οι αλλαγές αποθηκεύτηκαν επιτυχώς.',
                                         'default',
                                         array('class' => Flash::Success));
                $this->redirect(array('controller' => 'Offertypes', 'action' => 'index'));
            } else {
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                                         'default',
                                         array('class' => Flash::Error));
            }
        }
    }


    public function delete ($id = null) {

        if ($id == null) throw new BadRequestException();

        $options['conditions'] = array('OfferType.id' => $id);
        $type = $this->OfferType->find('first', $options);

        if (empty($type)) throw new NotFoundException();

        if ($this->OfferType->delete($id)) {
            $this->Session->setFlash('Η διαγραφή ήταν επιτυχής.');
            $this->redirect(array('controller' => 'Offertypes', 'action' => 'index'));
        } else {
            $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.');
        }
    }
}
