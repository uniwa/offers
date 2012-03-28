<?php

class OffersController extends AppController {

    public $name = 'Offers';
    public $uses = array('Offer', 'Company', 'Image', 'WorkHour', 'Day', 'Coupon', 'Student');
    public $paginate = array(
//        'fields' => array('Offer.title', 'Offer.description'),
        'limit' => 6,
        'order'=>array(

            'Offer.starting' => 'desc'
        ),
        'recursive' => -1
    );

    public $helpers = array('Html');

    function beforeFilter(){
        parent::beforeFilter();
        $this->Auth->allow( 'index' );
    }

    public function index() {
        //get last 3 happy  hour offers
        $happyOffers = $this->Offer->find( 'all' , array(
            'conditions'=>array('Offer.offer_type_id' => 1,
                            'Offer.offer_state_id' => OfferStates::Active,
                            'Offer.is_spam' => 0
                            ),
            'limit' => 3,
            'recursive' => -1,
            'order' => 'Offer.starting DESC'

        ) );

        //minify description strings
        $this->minify_desc( $happyOffers, 100 );
        $this->set( 'happyOffers', $happyOffers );

        $offers = $this->paginate('Offer', array(
                                    'Offer.offer_type_id !=' => 1,
                                    'Offer.offer_state_id' => OfferStates::Active,
                                    'Offer.is_spam' => 0
                                ) );
        $this->minify_desc( $offers, 160 );
        $this->set('offers', $offers);
    }

    private function minify_desc( &$array, $limit ) {

        foreach( $array as &$rec ) {

            $rec['Offer']['description'] = substr( $rec['Offer']['description'] ,0 , $limit );
        }

    }


    public function view($id = null) {

        $options['conditions'] = array(
                                    'Offer.id' => $id,
        //TODO uncomment the next line when the offer activation logic is
        // implemented
                                    //'Offer.offer_state_id' => OfferStates::Active,
                                    //'Offer.is_spam' => 0
                                    'Company.is_enabled' => 1
                                 );
        //TODO check if the company's user is_banned before showing the offer
        $options['recursive'] = 1;
        $offer = $this->Offer->find('first', $options);
        $this->set('offer', $offer);
        if (empty($offer))
            throw new NotFoundException('Η προσφορά δεν βρέθηκε.');

        if ($this->Auth->User('role') === 'student') {
            $st_opts['conditions'] = array('Student.id' => $this->Auth->User('id'));
            $st_opts['recursive'] = -1;
            $student = $this->Student->find('first', $st_opts);
            $this->set('student', $student);
        }
    }


    public function add() {

        if ($this->Auth->User('role') !== 'company')
            throw new ForbiddenException();

        // required to fill the select boxes with the correct values
        $this->set('offerTypes', $this->Offer->OfferType->find('list'));
        $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));
        $this->set('days', $this->Day->find('list'));

        if (!empty($this->request->data)) {

            // set the required default values
            $this->request->data['Offer']['current_quantity'] = 0;
            $this->request->data['Offer']['offer_state_id'] = OfferStates::Draft;

            // find the id of the Company related to the logged user
            // and assign it to Offer.company_id
            $options['fields'] = array('Company.id');
            $options['conditions'] = array(
                'Company.user_id' => $this->Auth->User('id')
            );
            $options['recursive'] = -1;
            $company = $this->Company->find('first', $options);
            $this->request->data['Offer']['company_id'] = $company['Company']['id'];

            $transaction = $this->Offer->getDataSource();
            $transaction->begin();
            $error = false;

            if ($this->Offer->save($this->request->data)) {

                $photos = $this->Image->process($this->request->data['Image'],
                                         array('offer_id' => $this->Offer->id));
                // try to save images
                if (!empty($photos) && !$this->Image->saveMany($photos))
                    $error = true;

                // try to save WorkHours only if Offer.category is HappyHour
                if ($this->request->data['Offer']['offer_type_id'] == 1) {
                    if (isset($this->request->data['WorkHour']) && !empty($this->request->data['WorkHour'])) {
                        for ($i = 0; $i < count($this->request->data['WorkHour']); $i++)
                            $this->request->data['WorkHour'][$i]['offer_id'] = $this->Offer->id;

                        if (!$this->WorkHour->saveMany($this->request->data['WorkHour']))
                            $error = true;
                    } else
                        $error = true;
                }
            }

            if ($error === true) {
                $transaction->rollback();
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα',
                                         'default',
                                         array('class' => Flash::Error));
            } else {
                $transaction->commit();
                $this->Session->setFlash('Η προσφορά αποθηκεύτηκε',
                                         'default',
                                         array('class' => Flash::Success));
                $this->redirect(array(
                                    'controller' => 'companies',
                                    'action' => 'view',
                                    $company['Company']['id']
                                ));
            }
        }
    }


    public function edit($id = null) {

        if ($id == null) throw new BadRequestException();

        $options['conditions'] = array('Offer.id' => $id);
        $options['recursive'] = 0;
        $offer = $this->Offer->find('first', $options);

        if (empty($offer)) throw new NotFoundException();

        if ($offer['Company']['user_id'] != $this->Auth->User('id'))
            throw new ForbiddenException();

        // required to fill the select boxes with the correct values
        $this->set('offerTypes', $this->Offer->OfferType->find('list'));
        $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));
        $this->set('days', $this->Day->find('list'));
        $this->set('work_hour_count', $offer['Offer']['work_hour_count'] );

        if (empty($this->request->data)) {

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
            $this->request->data['Offer']['current_quantity'] = 0;
            $this->request->data['Offer']['offer_state_id'] = OfferStates::Draft;

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

            if (!$this->Offer->save($this->request->data))
                $error = true;

            $del_opts['WorkHour.offer_id'] = $this->Offer->id;
            if (!$this->WorkHour->deleteAll($del_opts, true, true))
                $error = true;

            // try to save the new images
            $photos = $this->Image->process($this->request->data['Image'],
                                     array('offer_id' => $this->Offer->id));
            if (!empty($photos) && !$this->Image->saveMany($photos))
            $error = true;

            // If Offer.category is HappyHour delete all the related
            // images and insert new entries
            if ($this->request->data['Offer']['offer_type_id'] == 1) {

                if (isset($this->request->data['WorkHour']) && !empty($this->request->data['WorkHour'])) {
                    for ($i = 0; $i < count($this->request->data['WorkHour']); $i++)
                        $this->request->data['WorkHour'][$i]['offer_id'] = $this->Offer->id;

                    if (!$this->WorkHour->saveMany($this->request->data['WorkHour']))
                        $error = true;
                } else
                    // TODO show a better error message, because it is
                    // imandatory to insert WorkHours when the offer is happy hour
                    $error = true;
            }

            if ($error === true) {
                $transaction->rollback();
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα',
                                         'default',
                                         array('class' => Flash::Error));
            } else {
                $transaction->commit();
                $this->Session->setFlash('Η προσφορά αποθηκεύτηκε',
                                         'default',
                                         array('class' => Flash::Success));
                $this->redirect(array('controller' => 'offers',
                                      'action' => 'view',
                                      $this->Offer->id));
            }
        }
    }


    public function delete($id = null) {
        // An Offer can be delete only if it's draft.
        // At first, attempt to delete all Images and WorkHours
        // related to this Offer and then delete Offer.

        $options['conditions'] = array('Offer.id' => $id);
        $offer = $this->Offer->find('first', $options);

        if ($this->Auth->User('id') === $offer['Company']['user_id']) {
            if ($offer['Offer']['offer_state_id'] == OfferStates::Draft) {
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
                    $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                                             'default',
                                             array('class' => Flash::Error));
                    $this->redirect(array(
                                        'controller' => 'offers',
                                        'action' => 'view',
                                        $offer['Offer']['id']));
                } else {
                    $transaction->commit();
                    $this->Session->setFlash('Η προσφορά διαγράφηκε επιτυχώς.',
                                             'default',
                                             array('class' => Flash::Success));
                    $this->redirect(array(
                                        'controller' => 'companies',
                                        'action' => 'view',
                                        $offer['Company']['id']));
                }
            } else {
                $this->Session->setFlash('Η προσφορά δεν μπορεί να διαγραφεί',
                                         'default',
                                         array('class' => Flash::Info));
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
