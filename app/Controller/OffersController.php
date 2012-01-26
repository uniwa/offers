<?php

class OffersController extends AppController {

    public $name = 'Offers';
    public $helpers = array('Form');
    public $uses = array('Offer', 'Company', 'Image');


    public function view($id = null) {

        $options['conditions'] = array(
                                    'Offer.id' => $id,
        //TODO uncomment the next line when the offer activation logic is
        // implemented
        //                            'Offer.is_draft' => 0,
                                    'Company.is_enabled' => 1
                                 );
        //TODO check if the company's user is_banned before showing the offer
        $options['recursive'] = 1;
        $offer = $this->Offer->find('first', $options);
        $this->set('offer', $offer);
        if (empty($offer))
            throw new NotFoundException('Η προσφορά δεν βρέθηκε.');
    }


    public function add() {
        $this->set('offerTypes', $this->Offer->OfferType->find('list'));
        $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));

        if (!empty($this->data)) {

            $this->request->data['Offer']['is_active'] = 0;
            $this->request->data['Offer']['current_quantity'] = 0;
            $this->request->data['Offer']['is_draft'] = 1;

            $options['fields'] = array('Company.id');
            $options['conditions'] = array(
                'Company.user_id' => $this->Auth->User('id')
            );
            $options['recursive'] = -1;
            $company_id = $this->Company->find('first', $options);
            $this->request->data['Offer']['company_id'] = $company_id['Company']['id'];

            // if the user uploaded an image, store the required information
            // in $photo, so as to save it later
            // TODO autogenerate thumbnails for mobile app
            $photo = array();
            if (is_uploaded_file($this->data['Offer']['image']['tmp_name'])) {
                if ($this->isImage($this->data['Offer']['image']['type'])) {

                    $file = fread(fopen($this->data['Offer']['image']['tmp_name'], 'r'),
                                  $this->data['Offer']['image']['size']);
                    $this->isImage($this->data['Offer']['image']['type']);
                    $photo['Image'] = $this->data['Offer']['image'];
                    $photo['Image']['data'] = base64_encode($file);
                    //TODO change the hardcoded image category
                    $photo['Image']['image_category_id'] = 1;
                } else {
                    $this->Session->setFlash('Μη αποδεκτός τύπος αρχείου εικόνας.');
                    return;
                }
            }

            unset($this->request->data['Offer']['image']);

            $transaction = $this->Offer->getDataSource();
            $transaction->begin();

            $error = false;
            if ($this->Offer->save($this->data)) {
                if (!empty($photo)) {
                    $photo['Image']['offer_id'] = $this->Offer->id;
                    if (!$this->Image->save($photo))
                        $error = true;
                }
            } else {
                $error = true;
            }

            if ($error === true) {
                $transaction->rollback();
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα');
            } else {
                $transaction->commit();
                $this->Session->setFlash('Η προσφορά αποθηκεύτηκε');
            }
        }
    }
}
