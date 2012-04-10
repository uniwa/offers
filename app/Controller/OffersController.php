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

        if ($this->Auth->User('role') === ROLE_STUDENT) {
            $st_opts['conditions'] = array('Student.id' => $this->Auth->User('id'));
            $st_opts['recursive'] = -1;
            $student = $this->Student->find('first', $st_opts);
            $this->set('student', $student);
        }
    }

    // Wrapper functions for 'add offer' action
    public function add_happyhour() {
        $this->edit(0);
    }

    public function add_coupons() {
        $this->edit(1);
    }

    public function add_limited() {
        $this->edit(2);
    }

    // Wrapper functions for 'edit offer' action
    public function edit_happyhour($id=null) {
        $this->edit(0, $id);
    }

    public function edit_coupons($id=null) {
        $this->edit(1, $id);
    }

    public function edit_limited($id=null) {
        $this->edit(2, $id);
    }

    // Function for adding/editing offer
    // $offer_type_id same as global, 0-happy hour, 1-coupons, 2-limited
    // if $id is -1, add a new offer
    // else edit the offer with the corresponding id
    private function edit($offer_type_id, $id=null) {

        if ($this->Auth->User('role') !== ROLE_COMPANY)
            throw new ForbiddenException();

        if (is_null($id)) throw new BadRequestException();

        if (!empty($this->request->data)) {

            // set the required default values
            $this->request->data['Offer']['current_quantity'] = 0;
            $this->request->data['Offer']['offer_state_id'] = OfferStates::Draft;
            $this->request->data['Offer']['offer_type_id'] = $offer_type_id;

            // find the id of the Company related to the logged user
            // and assign it to Offer.company_id
            $options['fields'] = array('Company.id');
            $options['conditions'] = array(
                'Company.user_id' => $this->Auth->User('id')
            );
            $options['recursive'] = -1;
            $company = $this->Company->find('first', $options);
            $this->request->data['Offer']['company_id'] = $company['Company']['id'];

            $this->Offer->id = $id;
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
                if ($this->request->data['Offer']['offer_type_id'] == 0) {
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
        } else {
            // Edit existing offer
            if ($id !== -1) {
                $options['conditions'] = array('Offer.id' => $id);
                $options['recursive'] = 0;
                $offer = $this->Offer->find('first', $options);

                if (empty($offer)) throw new NotFoundException();

                if ($offer['Offer']['offer_type_id'] != $offer_type_id)
                    throw new BadRequestException();
                if ($offer['Company']['user_id'] != $this->Auth->User('id'))
                    throw new ForbiddenException();

                // required to fill the select boxes with the correct values
                $this->set('work_hour_count', $offer['Offer']['work_hour_count'] );

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
            }

            // Required to fill the select boxes with the correct values
            $this->set('offerTypes', $this->Offer->OfferType->find('list'));
            $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));
            $this->set('days', $this->Day->find('list'));

            // Common elements for all offer types
            $input_elements = array();

            $new_elem = array();
            $new_elem['title'] = 'Offer.title';
            $new_elem['options']['label'] = 'Τίτλος';
            $new_elem['options']['type'] = 'text';
            $input_elements[] = $new_elem;

            $new_elem = array();
            $new_elem['title'] = 'Offer.description';
            $new_elem['options']['label'] = 'Περιγραφή';
            $new_elem['options']['type'] = 'textarea';
            $input_elements[] = $new_elem;

            $new_elem = array();
            $new_elem['title'] = 'Image.0';
            $new_elem['options']['label'] = 'Φωτογραφία';
            $new_elem['options']['type'] = 'file';
            $input_elements[] = $new_elem;

            $new_elem = array();
            $new_elem['title'] = 'Offer.tags';
            $new_elem['options']['label'] = 'Λέξεις-κλειδιά';
            $new_elem['options']['type'] = 'text';
            $input_elements[] = $new_elem;

            $new_elem = array();
            $new_elem['title'] = 'Offer.offer_category_id';
            $new_elem['options']['label'] = 'Κατηγορία προσφοράς';
            $input_elements[] = $new_elem;

            // Coupons
            if ($offer_type_id == 1) {
                $new_elem = array();
                $new_elem['title'] = 'Offer.total_quantity';
                $new_elem['options']['label'] = 'Αριθμός διαθέσιμων κουπονιών';
                $new_elem['options']['empty'] = 'Δεν έχει κουπόνια';
                $input_elements[] = $new_elem;

                $new_elem = array();
                $new_elem['title'] = 'Offer.coupon_terms';
                $new_elem['options']['label'] = 'Όροι εξαργύρωσης κουπονιού';
                $new_elem['options']['type'] = 'text';
                $input_elements[] = $new_elem;
            }

            if (in_array($offer_type_id, array(1, 2))) {
                $new_elem = array();
                $new_elem['title'] = 'Offer.autostart';
                $new_elem['options']['label'] = 'Ημ/νία & ώρα έναρξης προσφοράς';
                $new_elem['options']['separator'] = ' ';
                $new_elem['options']['dateFormat'] = 'DMY';
                $new_elem['options']['minYear'] = date('Y');
                $new_elem['options']['maxYear'] = date('Y') + 1;
                $new_elem['options']['orderYear'] = 'asc';
                $new_elem['options']['timeFormat'] = '24';
                $new_elem['options']['interval'] = '15';
                $input_elements[] = $new_elem;
            }

            // Limited
            if ($offer_type_id == 2) {
                $new_elem = array();
                $new_elem['title'] = 'Offer.autoend';
                $new_elem['options']['label'] = 'Ημ/νία & ώρα λήξης προσφοράς';
                $new_elem['options']['separator'] = ' ';
                $new_elem['options']['dateFormat'] = 'DMY';
                $new_elem['options']['minYear'] = date('Y');
                $new_elem['options']['maxYear'] = date('Y') + 1;
                $new_elem['options']['orderYear'] = 'asc';
                $new_elem['options']['timeFormat'] = '24';
                $new_elem['options']['interval'] = '15';
                $input_elements[] = $new_elem;
            }

            $this->set('input_elements', $input_elements);

            $this->render('edit');
        }
    }


/*
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
*/

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
