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
        $this->Auth->allow('index');
        define('ADD', -1);
    }

    public function index() {
        //get last 3 happy hour offers
        $happyOffers = $this->Offer->find('all', array(
            'conditions'=>array(
                'Offer.offer_type_id' => 1,
                'Offer.offer_state_id' => OfferStates::Active,
                'Offer.is_spam' => 0),
            'limit' => 3,
            'recursive' => -1,
            'order' => 'Offer.started DESC'));

        //minify description strings
        $this->minify_desc($happyOffers, 100);
        $this->set('happyOffers', $happyOffers);

        $offers = $this->paginate('Offer', array(
            'Offer.offer_type_id !=' => 1,
            'Offer.offer_state_id' => OfferStates::Active,
            'Offer.is_spam' => 0));
        $this->minify_desc($offers, 160);
        $this->set('offers', $offers);
    }

    private function minify_desc( &$array, $limit ) {
        foreach($array as &$rec) {
            // the text to cut at a word-boundary for a maximum of $limit chars
            $desc = $rec['Offer']['description'];

            if (mb_strlen($desc) < $limit) continue;

            //find closest space near $limit
            $pos = mb_strpos($desc, ' ', $limit);
            if ($pos === false) {
                $pos = $limit;
            }
            // keep string from start till the space nearest to $limit
            $rec['Offer']['description'] =
                mb_substr($desc, 0, $pos, 'UTF-8') . '…';
        }
    }


    public function view($id = null) {
        $options['conditions'] = array(
            'Offer.id' => $id);

        // if role is admin, the offer is displayed no matter what
        if ($this->Auth->User('role') != ROLE_ADMIN) {
            $options['conditions']['OR'] = array(
                // this allows owner of offer to always view it
                'Company.user_id' => $this->Auth->User('id'),

                // these must apply for the rest of the members
                array(
                    'Offer.offer_state_id <>' => STATE_DRAFT,
                    'Offer.is_spam' => 0,
                    'Company.is_enabled' => 1
                )
            );
        }

        //TODO check if the company's user is_banned before showing the offer
        $options['recursive'] = 1;
        $offer = $this->Offer->find('first', $options);

        if (empty($offer))
            throw new NotFoundException('Η προσφορά δεν βρέθηκε.');

        $this->set('offer', $offer);

        if ($this->Auth->User('role') === ROLE_STUDENT) {
            $st_opts['conditions'] = array('Student.id' => $this->Auth->User('id'));
            $st_opts['recursive'] = -1;
            $student = $this->Student->find('first', $st_opts);
            $this->set('student', $student);
        }

        // Prepare information for view
        $offer_type_id = $offer['Offer']['offer_type_id'];
        $offer_info = array();
        $new_elem = array();
        $new_elem['label'] = "Τίτλος";
        $new_elem['value'] = $offer['Offer']['title'];
        $offer_info[] = $new_elem;
        $new_elem['label'] = "Περιγραφή";
        $new_elem['value'] = $offer['Offer']['description'];
        $offer_info[] = $new_elem;
        $new_elem['label'] = "Κατηγορία";
        $new_elem['value'] = $offer['OfferCategory']['name'];
        $offer_info[] = $new_elem;
        $new_elem['label'] = "Λέξεις-κλειδιά";
        $new_elem['value'] = $offer['Offer']['tags'];
        $offer_info[] = $new_elem;
        if (($offer_type_id == TYPE_COUPONS) &&
            $offer['Offer']['coupon_count'] >= 0) {
            $new_elem['label'] = "Δεσμευμένα κουπόνια";
            $new_elem['value'] = $offer['Offer']['coupon_count'].
                ' από '.$offer['Offer']['total_quantity'];
            $offer_info[] = $new_elem;
            $new_elem['label'] = "Όροι εξαργύρωσης κουπονιού";
            $new_elem['value'] = $offer['Offer']['coupon_terms'];
            $offer_info[] = $new_elem;
        }
        if ($offer_type_id == TYPE_LIMITED) {
            $new_elem['label'] = "Λήξη προσφοράς";
            $new_elem['value'] = $offer['Offer']['autoend'];
            $offer_info[] = $new_elem;
        }
        foreach($offer['WorkHour'] as $wh) {
            $new_elem['label'] = day($wh['day_id']);
            $new_elem['value'] = "{$wh['starting']} - {$wh['ending']}";
            $offer_info[] = $new_elem;
        }
        $this->set('offer_info', $offer_info);
/*
        $new_elem['label'] = "";
        $new_elem['value'] = $offer['Offer'][''];
        $offer_info[] = $new_elem;
        $new_elem['label'] = "";
        $new_elem['value'] = $offer['Offer'][''];
        $offer_info[] = $new_elem;
*/    }

    // Wrapper functions for 'add offer' action
    public function add_happyhour() {
        $this->modify(TYPE_HAPPYHOUR, ADD);
    }

    public function add_coupons() {
        $this->modify(TYPE_COUPONS, ADD);
    }

    public function add_limited() {
        $this->modify(TYPE_LIMITED, ADD);
    }

    // Wrapper functions for 'edit offer' action
    public function edit($id=null) {
        $this->modify(null, $id);
    }

    // Function for adding/editing offer
    // $offer_type_id same as global, 0-happy hour, 1-coupons, 2-limited
    // if $id is -1, add a new offer
    // else edit the offer with the corresponding id
    private function modify($offer_type_id, $id=null) {
        if ($this->Auth->User('role') !== ROLE_COMPANY)
            throw new ForbiddenException();

        if (is_null($id)) throw new BadRequestException();

        // Save modified offer
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

            $this->Offer->id = $id;
            $transaction = $this->Offer->getDataSource();
            $transaction->begin();
            $error = false;
            $saved = $this->Offer->save($this->request->data);

            if ($saved) {
                $photos = $this->Image->process(
                    $this->request->data['Image'],
                    array('offer_id' => $this->Offer->id));

                // try to save images
                if (!empty($photos) && !$this->Image->saveMany($photos))
                    $error = true;

                // try to save WorkHours only if Offer.category is HappyHour
                if ($this->request->data['Offer']['offer_type_id'] == TYPE_HAPPYHOUR) {
                    if (isset($this->request->data['WorkHour']) && !empty($this->request->data['WorkHour'])) {
                        for ($i = 0; $i < count($this->request->data['WorkHour']); $i++)
                            $this->request->data['WorkHour'][$i]['offer_id'] = $this->Offer->id;

                        if (!$this->WorkHour->saveMany($this->request->data['WorkHour']))
                            $error = true;
                    } else
                        $error = true;
                }
            } else {
                $error = true;
            }

            if ($error) {
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
                    $company['Company']['id']));
            }
        } else {
            // Add/edit offer
            if ($id !== -1) {
                // Edit existing offer
                $options['conditions'] = array('Offer.id' => $id);
                $options['recursive'] = 0;
                $offer = $this->Offer->find('first', $options);

                if (empty($offer)) throw new NotFoundException();

                if ($offer['Company']['user_id'] != $this->Auth->User('id'))
                    throw new ForbiddenException();

                // Set offer type
                $offer_type_id = $offer['Offer']['offer_type_id'];

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

            $this->request->data['Offer']['offer_type_id'] = $offer_type_id;
        }

        // Required to fill the select boxes with the correct values
        $this->set('offerTypes', $this->Offer->OfferType->find('list'));
        $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));
        $this->set('days', $this->Day->find('list'));

        $input_elements = $this->prepare_edit_view($offer_type_id);
        $this->set('input_elements', $input_elements);
        // Work hours for happy hour
        if ($offer_type_id == TYPE_HAPPYHOUR) {
            $work_hours = array(
                'work_hour_count' => 0,
                'timeFormat' => 24,
                //'interval' => 15 //default
                'header' => array('Ημέρα', 'Ώρα Έναρξης', 'Ώρα Λήξης', 'Επιλογή'),
                'table_class' => 'table table-striped',
                //'input_class' => span3, //default
                //'input_label' => null, //default
                //'staring_time_label' => null, //default
                //'ending_time_label' => null, //default
            );
            $this->set('work_hours', $work_hours);
        }
        $this->render('edit');
    }

    private function prepare_edit_view($offer_type_id) {
        // Common elements for all offer types
        $input_elements = array();

        $new_elem = array();
        $new_elem['title'] = 'Offer.offer_type_id';
        $new_elem['options']['type'] = 'hidden';
        $input_elements[] = $new_elem;

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
        if ($offer_type_id == TYPE_COUPONS) {
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

        if (in_array($offer_type_id, array(TYPE_COUPONS, TYPE_LIMITED))) {
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
        if ($offer_type_id == TYPE_LIMITED) {
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

        return $input_elements;
    }

    // Wrapper functions for `terminate' that specify the redirect target
    //
    // @param $id offer id to terminate
    public function terminate_from_company($id = null) {
        $this->terminate($id, array(
            'controller' => 'companies',
            'action' => 'view'));
    }
    public function terminate_from_offer($id = null) {
        $this->terminate($id, array(
            'controller' => 'offers',
            'action' => 'view', $id));
    }
    // this will (potentially) be used in the administrative page of all offers
#    public function terminate_from_admin($id = null) {
#    }

    // Responsible for declaring an offer as inactive.
    //
    // @param $id the offer to terminate
    // @param $redirect parameter to be passed into $this->redirect
    private function terminate($id = null, $redirect) {
        if (empty($id)) {
            throw new NotFoundException('Η προσφορά δεν βρέθηκε.');
        }

        // fetch only the fields required for checks and the ones to be updated
        // TODO see if just offer_state_id and ended can be fetched and have
        // this work still
        $fields = array(
            'Offer.*',
            //'Offer.offer_state_id',
            //'Offer.ended',
            'Company.user_id');
        // could use unbind on Coupon, Image and WorkHour to avoid Left joins !
        // using fields, empty arrays are returned for these 3 associations

        $offer = $this->Offer->read($fields, $id);

        if (empty($offer)) {
            throw new NotFoundException('Η προσφορά δεν βρέθηκε.');
        }

        // allow company owner and admins to terminate an offer
        if ($this->Auth->user('id') === $offer['Company']['user_id'] ||
            $this->Auth->user('role') === ROLE_ADMIN) {

            // only active offers may be terminated
            if ($offer['Offer']['offer_state_id'] == STATE_ACTIVE) {

                $this->Offer->set('offer_state_id', STATE_INACTIVE);
                $this->Offer->set('ended', date('Y-m-d H:i:s'));

                if ($this->Offer->save()) {
                    $this->Session->setFlash('Η προσφορά απενεργοποιήθηκε.');

                } else {
                    $this->Session->setFlash(
                        'Η προσφορά δεν κατέστη δυνατό να απενεργοποιηθεί.');
                }

                $this->redirect($redirect);

            } else {
                throw new ForbiddenException('Η προσφορά δεν δύναται απενεργοποίησης.');
            }
        } else {
            throw new ForbiddenException('Δεν έχετε πρόσβαση σε αυτή τη σελίδα.');
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
