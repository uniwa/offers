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


    // Wrapper function of modify()
    public function add () {
        $this->modify();
    }

    // Wrapper function of modify()
    public function edit ($id = null) {
        if ($id == null) throw new BadRequestException();
        $this->modify($id);
    }

    private function modify($id = null) {
        $is_add = $id == null;

        if (! $is_add) {

            // ascertain the existence of the id - get current id data
            $info = $this->OfferCategory->findById($id);
            if ($info == false)
                throw new NotFoundException();
        }

        if (empty($this->request->data)) {

            if (! $is_add) {
                // required for the presentation when editing
                $this->request->data = $info;
            }
        } else {

            // make sure that the specified name is not already in use
            $name = $this->request->data('OfferCategory.name');
            $options = array('conditions' => array('id <>' => $id,
                                                   'name' => $name));

            if ($this->OfferCategory->find('first', $options) != false) {
                // name is already in use
                $this->Session->setFlash(
                    'Η ονομασία αυτή χρησιμοποιείται ήδη',
                    'default',
                    array('class' => Flash::Error));
            } else {

                // avoid implicit id manipulation
                // NOTE: on ADD, id is NULL, so a valid one will be generated
                $this->request->data['OfferCategory']['id'] = $id;

                if ($this->OfferCategory->save($this->request->data)) {

                    $this->Session->setFlash(
                                        'Οι αλλαγές αποθηκεύτηκαν επιτυχώς.',
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

        $this->render('edit');
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
