<?php

class OffercategoriesController extends  AppController {

    public $name = 'Offercategories';
    public $uses = array('OfferCategory');
    public $helpers = array('Html', 'Form');


    public function beforeFilter () {
        parent::beforeFilter();
        if ($this->Auth->User('role') !== ROLE_ADMIN) {
            throw new ForbiddenException();
        }
    }


    public function index () {

        $offerCategories = $this->OfferCategory->find('all');
        $this->set('results', $offerCategories);
    }


    public function add () {
        if (!empty($this->request->data)) {
            if ($this->OfferCategory->save($this->request->data)) {
                $this->Session->setFlash('Η αποθήκευση ήταν επιτυχής.',
                                         'default',
                                         array('class' => Flash::Success));
                $this->redirect(array('controller' => 'offercategories',
                                      'action' => 'index'));
            } else {
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                                         'default',
                                         array('class' => Flash::Error));
            }
        }
    }


    public function edit ($id = null) {

        if ($id == null) throw new BadRequestException();

        if (empty($this->request->data)) {

            $options['conditions'] = array('OfferCategory.id' => $id);
            $type = $this->OfferCategory->find('first', $options);

            if (empty($type)) throw new NotFoundException();

            $this->request->data = $type;
        } else {

            if ($this->OfferCategory->save($this->request->data)) {
                $this->Session->setFlash('Οι αλλαγές αποθηκεύτηκαν επιτυχώς.',
                                         'default',
                                         array('class' => Flash::Success));
                $this->redirect(array('controller' => 'offercategories',
                                      'action' => 'index'));
            } else {
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                                         'default',
                                         array('class' => Flash::Error));
            }
        }
    }


    public function delete ($id = null) {

        if ($id == null) throw new BadRequestException();

        $options['conditions'] = array('OfferCategory.id' => $id);
        $type = $this->OfferCategory->find('first', $options);

        if (empty($type)) throw new NotFoundException();

        if ($this->OfferCategory->delete($id)) {
            $this->Session->setFlash('Η διαγραφή ήταν επιτυχής.',
                                     'default',
                                     array('class' => Flash::Success));
            $this->redirect(array('controller' => 'offercategories', 'action' => 'index'));
        } else {
            $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                                     'default',
                                     array('class' => Flash::Error));
        }
    }
}
