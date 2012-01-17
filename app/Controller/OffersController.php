<?php

class OffersController extends AppController {

    public $name = 'Offers';
    public $helpers = array('Form');
    public $uses = array('Offer', 'Company');


    public function add() {
        $this->set('offerTypes', $this->Offer->OfferType->find('list'));
        $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));

        if (!empty($this->data)) {

            $this->request->data['Offer']['is_active'] = 0;
            $this->request->data['Offer']['current_quantity'] = 0;
            $this->request->data['Offer']['is_draft'] = 1;
            $company_id = $this->Company->find('first', array(
                                    'fields' => array('Company.id'),
                                    'conditions' => array(
                                        'Company.user_id' =>
                                                    $this->Auth->User('id')
                                    ),
                                    'recursive' => -1
                                ));
            $this->request->data['Offer']['company_id'] = $company_id['Company']['id'];

            if (is_uploaded_file($this->data['Offer']['photo']['tmp_name'])) {
                $photo = fread(fopen($this->data['Offer']['photo']['tmp_name'], 'r'),
                                     $this->data['Offer']['photo']['size']);
                $this->request->data['Offer']['photo'] = base64_encode($photo);
            } else {
                $this->request->data['Offer']['photo'] = null;
            }

            if ($this->Offer->save($this->data)) {
                $this->Session->setFlash('Η προσφορά αποθηκεύτηκε');
            } else {
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα');
            }
        }
    }
}
