<?php

class OffersController extends AppController {

    public $name = 'Offers';
    public $helpers = array('Form');
    public $uses = array('Offer', 'Company', 'Image', 'WorkHour', 'Day');

    public $paginate = array(
        'fields' => array('Offer.title', 'Offer.description'),
        'limit' => 6,
        'order'=>array(

            'Offer.starting' => 'desc'
        )
    );

    function beforeFilter(){
        parent::beforeFilter();
        $this->Auth->allow( 'index' );
    }

    public function index() {
        $options['conditions'] = array(
                                    'Offer.is_draft' => 0,
                                    'Offer.is_active' => 1);
        $options['recursive'] = -1;
        $offers = $this->paginate('Offer', $options['conditions']);
        $this->set('offers', $offers);
    }


    public function view($id = null) {

        $options['conditions'] = array(
                                    'Offer.id' => $id,
        //TODO uncomment the next line when the offer activation logic is
        // implemented
//                                     'Offer.is_draft' => 0,
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

        // required to fill the select boxes with the correct values
        $this->set('offerTypes', $this->Offer->OfferType->find('list'));
        $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));
        $this->set('days', $this->Day->find('list'));

        if (!empty($this->data)) {

            // set the required default values
            $this->request->data['Offer']['is_active'] = 0;
            $this->request->data['Offer']['current_quantity'] = 0;
            $this->request->data['Offer']['is_draft'] = 1;

            // find the id of the Company related to the logged user
            // and assign it to Offer.company_id
            $options['fields'] = array('Company.id');
            $options['conditions'] = array(
                'Company.user_id' => $this->Auth->User('id')
            );
            $options['recursive'] = -1;
            $company_id = $this->Company->find('first', $options);
            $this->request->data['Offer']['company_id'] = $company_id['Company']['id'];

            $transaction = $this->Offer->getDataSource();
            $transaction->begin();
            $error = false;

            if ($this->Offer->save($this->data)) {

                $photos = $this->processImages($this->request->data['Image']);
                // try to save images
                if (!empty($photos)) {
                    for ($i = 0; $i < count($photos); $i++)
                        $photos[$i]['offer_id'] = $this->Offer->id;

                    if (!$this->Image->saveMany($photos))
                        $error = true;
                }

                // try to save WorkHours only if Offer.category is HappyHour
                if ($this->data['Offer']['offer_category_id'] == 1) {
                    for ($i = 0; $i < count($this->data['WorkHour']); $i++)
                        $this->request->data['WorkHour'][$i]['offer_id'] = $this->Offer->id;

                    if (!$this->WorkHour->saveMany($this->data['WorkHour']))
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


    public function edit($id = null) {

        if ($id == null) throw new BadRequestException();

        $options['conditions'] = array(
                                    'Offer.id' => $id,
                                    'Offer.is_draft' => 1);
        $options['recursive'] = 0;
        $offer = $this->Offer->find('first', $options);

        if (empty($offer)) throw new NotFoundException();

        if ($offer['Company']['user_id'] != $this->Auth->User('id'))
            throw new ForbiddenException();

        // required to fill the select boxes with the correct values
        $this->set('offerTypes', $this->Offer->OfferType->find('list'));
        $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));
        $this->set('days', $this->Day->find('list'));

        if (empty($this->data)) {

            // find the images of this offer and put them in $offer variable
            if ($offer['Offer']['image_count'] > 0) {
                $img_opts['conditions'] = array('Image.offer_id' => $offer['Offer']['id']);
                $img_opts['recursive'] = -1;
                $offer['Image'] = Set::extract('/Image/.',
                                               $this->Image->find('all', $img_opts));
            }

            // find the work_hours of this offer and put them in $offer variable
            if ($offer['Offer']['work_hour_count'] > 0) {
                $wh_opts['conditions'] = array('WorkHour.offer_id' => $offer['Offer']['id']);
                $wh_opts['recursive'] = -1;
                $offer['WorkHour'] = Set::extract('/WorkHour/.',
                                                  $this->WorkHour->find('all', $wh_opts));
            }

            $this->request->data = $offer;
        } else {
            // set the required default values
            $this->request->data['Offer']['is_active'] = 0;
            $this->request->data['Offer']['current_quantity'] = 0;
            $this->request->data['Offer']['is_draft'] = 1;

            // find the id of the Company related to the logged user
            // and assign it to Offer.company_id
            $options['fields'] = array('Company.id');
            $options['conditions'] = array(
                'Company.user_id' => $this->Auth->User('id')
            );
            $options['recursive'] = -1;
            $company_id = $this->Company->find('first', $options);
            $this->request->data['Offer']['company_id'] = $company_id['Company']['id'];

            $transaction = $this->Offer->getDataSource();
            $transaction->begin();
            $error = false;

            if ($this->Offer->save($this->data)) {

                // try to save the new images
                $photos = $this->processImages($this->request->data['Image']);
                if (!empty($photos)) {
                    for ($i = 0; $i < count($photos); $i++)
                        $photos[$i]['offer_id'] = $this->Offer->id;

                    if (!$this->Image->saveMany($photos))
                        $error = true;
                }

                // If Offer.category is HappyHour delete all the related
                // images and insert new entries
                if ($this->data['Offer']['offer_category_id'] == 1) {
                    $del_opts['WorkHour.offer_id'] = $this->Offer->id;
                    if ($this->WorkHour->deleteAll($del_opts, false)) {
                        for ($i = 0; $i < count($this->data['WorkHour']); $i++)
                            $this->request->data['WorkHour'][$i]['offer_id'] = $this->Offer->id;

                        if (!$this->WorkHour->saveMany($this->data['WorkHour']))
                            $error = true;
                    } else
                        $error = true;
                }
            } else {
                $error = true;
            }

// $transaction->rollback();pr($offer); die();

            if ($error === true) {
                $transaction->rollback();
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα');
            } else {
                $transaction->commit();
                $this->Session->setFlash('Η προσφορά αποθηκεύτηκε');
            }
        }
    }


    public function delete($id = null) {
        // An Offer can be delete only if is_draft == 1.
        // At first, attempt to delete all Images and WorkHours
        // related to this Offer and then delete Offer.

        $options['conditions'] = array('Offer.id' => $id);
        $offer = $this->Offer->find('first', $options);

        if ($this->Auth->User('id') === $offer['Company']['user_id']) {
            if ($offer['Offer']['is_draft'] == 1) {
                $transaction = $this->Offer->getDataSource();
                $transaction->begin();
                $error = false;

                if ($this->Image->deleteAll(array('Image.offer_id' => $id), false) &&
                    $this->WorkHour->deleteAll(array('WorkHour.offer_id' => $id), false))
                {
                    if (!$this->Offer->delete($id, false))
                        $error = true;
                } else {
                    $error = true;
                }

                if ($error === true) {
                    $transaction->rollback();
                    $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.');
                    $this->redirect(array(
                                        'controller' => 'offers',
                                        'action' => 'view',
                                        $offer['Offer']['id']));
                } else {
                    $transaction->commit();
                    $this->Session->setFlash('Η προσφορά διαγράφηκε επιτυχώς.');
                    $this->redirect(array(
                                        'controller' => 'companies',
                                        'action' => 'view',
                                        $offer['Company']['id']));
                }
            } else {
                $this->Session->setFlash('Η προσφορά δεν μπορεί να διαγραφεί');
                $this->redirect(array(
                                    'controller' => 'offers',
                                    'action' => 'view',
                                    $offer['Offer']['id']));
            }
        } else {
            throw new ForbiddenException('Δεν έχετε πρόσβαση σε αυτή τη σελίδα.');
        }
    }
}
