<?php

class OffercategoriesController extends  AppController {

    public $name = 'Offercategories';
    public $uses = array('OfferCategory', 'Offer');
    public $helpers = array('Html', 'Form');

    public function beforeFilter () {
        parent::beforeFilter();
        if ($this->Auth->User('role') !== ROLE_ADMIN) {
            throw new ForbiddenException();
        }
    }

    public function index () {

        // hasMany and hasAndBelongsToMany associations do NOT automatically
        // join, so we need to do it ourselves
        $joins = array(
                    array('table' => 'offers',
                          'alias' => 'Offer',
                          'type' => 'LEFT',
                          'conditions' => array(
                              'OfferCategory.id = Offer.offer_category_id',
                           )));

        $options = array('fields' => array('OfferCategory.id',
                                           'OfferCategory.name',
                                           'COUNT(Offer.id) as offer_count'),
                         'joins' => $joins,
                         'group' => 'OfferCategory.id',
                    );

        $offer_categories = $this->OfferCategory->find('all', $options);
        $this->set('results', $offer_categories);
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
        $this->set('is_add', $is_add);

        if (! $is_add) {

            // ascertain the existence of the id - get current id data
            $info = $this->OfferCategory->findById($id);
            if ($info == false) throw new NotFoundException();

            $this->set('offer_name', $info['OfferCategory']['name']);
        }

        if (empty($this->request->data)) {

            if (! $is_add) {
                // required for the presentation when editing
                $this->request->data = $info;
            }
        } else {

            // make sure that the specified name is not already in use
            $options = array('conditions' => array(
                         'id <>' => $id,
                         'name' => $this->request->data('OfferCategory.name')));

            $original = $this->OfferCategory->find('first', $options);
            if ($original != false) {
                $cat_name = $original['OfferCategory']['name'];
                // name is already in use
                $this->Session->setFlash(
                    "Η κατηγορία &laquo;{$cat_name}&raquo; υφίσταται ήδη",
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

        $this->render('modify');
    }

    public function delete ($id = null) {

        if ($id == null) throw new BadRequestException();

        $category = $this->OfferCategory->findById($id);
        if (empty($category)) throw new NotFoundException();

        // if (at least) an offer belongs to this category, then the latter may
        // not be deleted
        $this->Offer->recursive = -1;
        if ($this->Offer->findByOfferCategoryId($id, array('id')) != false) {
            $this->Session->setFlash(
                   'Η κατηγορία διαθέτει προσφορές και δεν μπορεί να διαγραφεί',
                   'default',
                    array('class' => Flash::Error));
        } else {
            if ($this->OfferCategory->delete($id)) {
                $this->Session->setFlash('Η διαγραφή ήταν επιτυχής.',
                                         'default',
                                         array('class' => Flash::Success));
            } else {
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                                         'default',
                                         array('class' => Flash::Error));
            }
        }
        $this->redirect(array('controller' => 'offercategories',
                              'action' => 'index'));
    }
}
