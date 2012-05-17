<?php

class OffersController extends AppController {

    public $name = 'Offers';
    public $uses = array('Offer', 'Company', 'Image', 'WorkHour', 'Day',
        'Coupon', 'Student', 'Vote');
    public $paginate = array(
//        'fields' => array('Offer.title', 'Offer.description'),
        'limit' => 6,
        'order'=>array(
            'Offer.starting' => 'desc'
        ),
        'recursive' => -1
    );

    public $helpers = array('Html', 'Time');

    public $components = array('Common', 'RequestHandler');

    function beforeFilter(){
        // this call should precede all actions that return data (exceptions
        // included)
        $this->api_initialize();

        if (! $this->is_authorized($this->Auth->user()))
            throw new ForbiddenException('Δεν επιτρέπεται η πρόσβαση');

        parent::beforeFilter();
        $this->Auth->allow('index');
        define('ADD', -1);
    }

    public function is_authorized($user) {
        $role = $this->Auth->user('role');
        $allow = array('index', 'category', 'view', 'happyhour', 'coupons',
            'limited', 'tag');
        $owner = array('edit', 'delete', 'imageedit', 'terminate_from_company',
            'terminate_from_offer', 'activate_from_company',
            'activate_from_offer');
        $companies = array('add_happyhour', 'add_coupons', 'add_limited',
            'webservice_add');
        $students = array('vote_up', 'vote_down');

        // All registered users can view offers
        if (in_array($this->action, $allow)) {
            return true;
        }

        // The owner of an offer can edit and delete it, as well as activate and
        //  terminate it
        if (in_array($this->action, $owner)) {

            // no id may have been supplied in the url
            if (array_key_exists(0,$this->request->params['pass'])) {

                $offer_id = $this->request->params['pass'][0];
                if ($this->Offer->is_owned_by($offer_id, $user['id'])) {
                    return true;
                }
            }
        }

        // Only companies
        if (in_array($this->action, $companies)) {
            if ($role === ROLE_COMPANY) {
                return true;
            }
        }

        return parent::is_authorized($user);
    }

    public function index() {
        $params = array('valid');
        $offers = $this->display($params, false);

        if ($this->is_webservice) {
            switch ($this->webservice_type) {
                case 'js':
                case 'json':
                    $data = $this->api_prepare_view($offers, false);
                    break;

                case 'xml':
                    $data = $this->api_prepare_view($offers);
                    break;
            }
            $this->api_compile_response(
                200, array( 'offers' => $data['offers'],
                            'companies' => $data['companies']));

        } else {
            $this->set('offers', $offers);
        }
    }

    public function happyhour() {
        $params = array('happyhour');
        $this->display($params);
    }

    public function coupons() {
        $params = array('coupons');
        $this->display($params);
    }

    public function limited() {
        $params = array('limited');
        $this->display($params);
    }

    public function tag($tag) {
        $params = array('tag', 'tag' => $tag);
        $this->display($params);
    }

    // Displays offers in list according to passed criteria and sorting params
    private function display($params, $render = true) {
        $this->paginate = $params;
        $offers = $this->paginate();
        $this->minify_desc($offers, 160);
        if ($render) {
            $this->set('offers', $offers);
            $this->render('index');
        } else {
            return $offers;
        }
    }

    public function category($id) {
        // TODO throw exception if invalid/non-existent id
        $id = (int)$id; // Sanitize id input
        $conditions['Offer.offer_category_id'] = $id;
        $this->paginate = array('valid', 'conditions' => $conditions);
        $offers = $this->paginate();
        $this->minify_desc($offers, 160);
        $this->set('offers', $offers);
        $this->render('index');
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
        $options['conditions'] = array('Offer.id' => $id);

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

        if (empty($offer)) {
            throw new NotFoundException('Η προσφορά δεν βρέθηκε');
        }

        $this->set('offer', $offer);

        if ($this->Auth->User('role') === ROLE_STUDENT) {
            $st_opts['conditions'] = array('Student.id' => $this->Auth->User('id'));
            $st_opts['recursive'] = -1;
            $student = $this->Student->find('first', $st_opts);
            $this->set('student', $student);
        }

        //get coupons for offer if user is owner
        if ($this->Offer->is_owned_by($id, $this->Auth->User('id'))) {
            $fields = array('Coupon.id', 'Coupon.serial_number', 'Coupon.created');
            $order = array('Coupon.created DESC');
            $coupons = $this->Offer->Coupon->find('all',
                array('fields' => $fields, 'order' => $order));
            $this->set('is_owner', true);
            $this->set('coupons', $coupons);
        }

        if ($this->is_webservice) {
            switch ($this->webservice_type) {
                case 'js':
                case 'json':
                    $offer_info = $this->api_prepare_view($offer, false);
                    break;

                case 'xml':
                    $offer_info = $this->api_prepare_view($offer);
                    break;
            }
            $this->api_compile_response(200, array(
                'offer' => $offer_info['offer'],
                'company' => $offer_info['company']));

        } else {
            // Prepare information for view
            $offer_info = $this->prepare_view($offer);
            $this->set('offer_info', $offer_info);
            $student_id = $this->Session->read('Auth.Student.id');
            $options['conditions'] = array(
                'Vote.offer_id' => $id,
                'Vote.student_id' => $student_id);
            $options['recursive'] = -1;
            $vote = $this->Vote->find('first', $options);
            $this->set('student_vote', $vote['Vote']['vote']);
        }
    }

    private function prepare_view($offer) {
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
        $vote_count = $offer['Offer']['vote_count'];
        $vote_class = ($vote_count >= 0)?'green':'red';
        $votes = "<span class='votes {$vote_class}'>{$vote_count}</span>";
        $new_elem['label'] = "Άθροισμα ψήφων";
        $new_elem['value'] = $votes;
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
            $max = $offer['Offer']['max_per_student'];
            $new_elem['label'] = "Μέγιστος αριθμός κουπονιών ανά σπουδαστή";
            $new_elem['value'] =
                ($max == BIND_UNLIMITED)?'απεριόριστα':$max;
            $offer_info[] = $new_elem;
            $new_elem['label'] = "Όροι εξαργύρωσης κουπονιού";
            $new_elem['value'] = $offer['Offer']['coupon_terms'];
            $offer_info[] = $new_elem;
        }
        if ($offer_type_id == TYPE_LIMITED) {
            App::uses('CakeTime', 'Utility');
            $new_elem['label'] = "Λήξη προσφοράς";
            $new_elem['value'] = CakeTime::format('d-m-Y H:i',
                $offer['Offer']['autoend']);
            $offer_info[] = $new_elem;
        }
        foreach($offer['WorkHour'] as $wh) {
            $new_elem['label'] = day($wh['day_id']);
            $wh['starting'] = $this->trim_time($wh['starting']);
            $wh['ending'] = $this->trim_time($wh['ending']);
            $new_elem['value'] = "{$wh['starting']} - {$wh['ending']}";
            $offer_info[] = $new_elem;
        }
        return $offer_info;
    }

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

    // The purpose of this function is to replace the wrapper functions add_*,
    // but this time for the webservice api. All it does is to call `modify'
    // with the appropriate parameters (offer_type_id, and id == ADD) so the
    // creation of a new offer may take place.
    public function webservice_add($param = null) {

        $is_supported = $this->RequestHandler->prefers(array('xml','json', 'js'));

        // this function is available to webservice api calls only
        if ($is_supported) {

            $request = $this->request->data;

            if (!empty($request)) {

                // remove wrapping
                // later on, xsd-compliance checks must also be performed
                $data = reset($request);

                if (Set::check($data, 'Offer.offer_type_id')) {
                    $this->modify($data['Offer']['offer_type_id'], ADD);
                    return;
                }
            }
        }

        throw new BadRequestException(
            'Η δομή του αιτήματος δεν είναι η αναμενόμενη');
    }

    // Wrapper functions for 'edit offer' action
    public function edit($id=null) {
        $this->modify(null, $id);
    }

    // Function for adding/editing offer
    // $offer_type_id same as global, 1-happy hour, 2-coupons, 3-limited
    // if $id is -1, add a new offer
    // else edit the offer with the corresponding id
    private function modify($offer_type_id, $id=null) {
        if (is_null($id)) {
            throw new BadRequestException(
                'Δεν έχει προσδιοριστεί το id της προσφοράς');
        }

        // special treatment for xml
        $is_xml = $this->RequestHandler->requestedWith('xml');

        // Save modified offer
        if (!empty($this->request->data)) {

            // extract data from XML request
            if ($is_xml) {
                // remove unnecessary root (wrapping) element
                $request_data = $this->request->data;
                $this->request->data = reset($request_data);
            }

            // avoid blindly accepting values for all properties
            $this->filter_fields($this->request->data);

            // ALWAYS set type, even if it's null (in which case
            // `Offer::beforeValidate' will remove it). This way, the field will
            // not be updated even if it was specified in the request.
            $this->request->data['Offer']['offer_type_id'] = $offer_type_id;

            $this->set_default_values($this->request->data);

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
                $request_data = $this->request->data;
                // try to save WorkHours only if Offer.category is HappyHour
                if (array_key_exists('offer_type_id', $request_data['Offer']) &&
                    $request_data['Offer']['offer_type_id'] == TYPE_HAPPYHOUR) {

                    if (isset($this->request->data['WorkHour']) &&
                        !empty($this->request->data['WorkHour'])) {
                        $input_hours = $this->request->data['WorkHour'];
                        $work_hours = array();
                        for ($i = 1; $i <= count($input_hours); $i++) {
                            if (!empty($input_hours[$i]['starting']) &&
                                !empty($input_hours[$i]['ending'])) {
                                $h0 = $this->get_time($input_hours[$i]['starting']);
                                $h1 = $this->get_time($input_hours[$i]['ending']);
                                $work_hours[] = array(
                                    'offer_id' => $this->Offer->id,
                                    'day_id' => ''.$i,
                                    'starting' => $h0,
                                    'ending' => $h1);
                            }
                        }
                        if (!$this->WorkHour->deleteAll(
                            array('Offer.id' => $this->Offer->id), false)) {
                            $error = true;
                        } else {
                            if (!$this->WorkHour->saveMany($work_hours))
                                $error = true;
                        }
                    } else
                        $error = true;
                }
            } else {
                $error = true;
            }

            if ($error) {
                $transaction->rollback();

                $this->notify(
                    array(  'Παρουσιάστηκε κάποιο σφάλμα',
                            'default',
                            array('class' => Flash::Error)),
                    null, 400, $this->Offer->validationErrors);
            } else {
                $transaction->commit();

                $this->notify(
                    // the message to appear (parameters of `setFlash')
                    array(  'Η προσφορά αποθηκεύτηκε',
                            'default',
                            array('class' => Flash::Success)),
                    // parameters of `redirect' (in case of html response)
                    array(  array(  'controller' => 'companies',
                                    'action' => 'view',
                                    $company['Company']['id'])),
                    // status of response (in case of webservice api call)
                    200,
                    // additional info (in case of webservice api call)
                    array(  'id' => $this->Offer->id));
            }
        } else {

            if ($this->is_webservice) {
                // if data is empty, webservice call should be terminated
                return $this->notify(
                    'Η δομή του αιτήματος δεν είναι η αναμενόμενη', null, 400);
            }

            // Add/edit offer
            if ($id !== -1) {
                // Edit existing offer
                $options['conditions'] = array('Offer.id' => $id);
                $options['recursive'] = 0;
                $offer = $this->Offer->find('first', $options);

                if (empty($offer)) throw new NotFoundException();

                if ($offer['Company']['user_id'] != $this->Auth->User('id'))
                    throw new ForbiddenException();

                if ($offer['Offer']['offer_state_id'] != STATE_DRAFT)
                    throw new ForbiddenException();

                // Set offer type
                $offer_type_id = $offer['Offer']['offer_type_id'];

                // required to fill the select boxes with the correct values
                $this->set('work_hour_count', $offer['Offer']['work_hour_count']);

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
                    $offer['WorkHour'] = Set::extract(
                        '/WorkHour/.', $this->WorkHour->find('all', $wh_opts));

                    // populate all 7 days of the week for view input
                    $fill_keys = array('starting','ending','offer_id');
                    $fill_values = array('','',$offer['Offer']['id']);
                    $fill_day = array_combine($fill_keys, $fill_values);
                    $fill_week = array_fill(1, 7, $fill_day);

                    // trim ':00' seconds from time and update day
                    foreach ($offer['WorkHour'] as $k => $wh) {
                        $offer['WorkHour'][$k]['starting'] = $this->trim_time($wh['starting']);
                        $offer['WorkHour'][$k]['ending'] = $this->trim_time($wh['ending']);
                        $fill_week[$wh['day_id']] = $offer['WorkHour'][$k];
                    }
                    $offer['WorkHour'] = $fill_week;
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
/*
        $new_elem = array();
        $new_elem['title'] = 'Image.0';
        $new_elem['options']['label'] = 'Εικόνα';
        $new_elem['options']['type'] = 'file';
        $input_elements[] = $new_elem;
*/
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

            $max_options = array(
                BIND_UNLIMITED => 'απεριόριστα', 1 => '1', 2 => '2',
                3 => '3', 5 => '5', 10 => '10');
            $new_elem = array();
            $new_elem['title'] = 'Offer.max_per_student';
            $new_elem['options']['label'] = "Μέγιστος αριθμός κουπονιών ανά φοιτητή<br />";
            $new_elem['options']['type'] = 'select';
            $new_elem['options']['options'] = $max_options;
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
            $new_elem['options']['empty'] = true;
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
            $new_elem['options']['empty'] = true;
            $input_elements[] = $new_elem;
        }

        return $input_elements;
    }

    // Unsets properties that should not be present in create or update
    // requests.
    //
    // @param data to filter; typically, $this->request->data
    private function filter_fields(&$data) {
        $offer = array(
            'id', 'started', 'ended', 'coupon_count', 'image_count', 'is_spam',
            'work_hour_count', 'offer_state_id', 'created', 'modified');

        foreach ($offer as $property) {
            if (array_key_exists($property, $data['Offer'])) {
                unset($data['Offer'][$property]);
            }
        }
    }

    // Makes necessary initializations such as default values for all offers
    // and dummy values for properties that should not be required for the
    // current offer).
    //
    // @param $d data to alter; typically, $this->request->data
    private function set_default_values(&$d) {

        // make alterations to non-coupon offers so that they pass validation of
        // coupon properties
        if ($d['Offer']['offer_type_id'] != TYPE_COUPONS) {

            $d['Offer']['total_quantity'] = 1;
            $d['Offer']['max_per_student'] = '0';
        }

        // set the required default values
        $d['Offer']['current_quantity'] = 0;
        $d['Offer']['offer_state_id'] = STATE_DRAFT;
    }

    // Images administration
    public function imageedit($id = null) {
        if (is_null($id))
            throw new BadRequestException();

        // Get offer
        $options['conditions'] = array('Offer.id' => $id);
        $options['recursive'] = 1;
        $offer = $this->Offer->find('first', $options);
        $max_images = count($offer['Image']) >= MAX_IMAGES;

        if (empty($offer)) throw new NotFoundException();

        if ($offer['Company']['user_id'] != $this->Auth->User('id'))
            throw new ForbiddenException();

        if ($offer['Offer']['offer_state_id'] != STATE_DRAFT)
            throw new ForbiddenException();

        $this->set('offer', $offer);

        if (!$max_images) {
            $new_elem = array();
            $new_elem['title'] = 'Image';
            $new_elem['options']['label'] = 'Προσθήκη εικόνας';
            $new_elem['options']['type'] = 'file';
            $input_elements[] = $new_elem;

            $this->set('input_elements', $input_elements);
        }

        if (!empty($this->request->data) && !$max_images) {
            // check if user pressed upload without image
            if (empty($this->request->data['Image']['name']))
                $this->upload_error($id, 'empty');

            // check if image is uploaded
            if (!is_uploaded_file($this->request->data['Image']['tmp_name'])) {
                $this->upload_error($id, 'size');
            } else {
                $tmp_size = filesize($this->request->data['Image']['tmp_name']);
                if ($tmp_size > MAX_UPLOAD_SIZE)
                    $this->upload_error($id, 'size');
            }

            // check file type
            if (!$this->valid_type($this->data['Image']['tmp_name']))
                $this->upload_error($id, 'filetype');

            $photo = $this->Image->process($this->request->data['Image'],
                array('offer_id' => $id));
            // add company_id
            $company_id = $this->Session->read('Auth.Company.id');
            $photo['company_id'] = $company_id;

            // try to save images
            $transaction = $this->Image->getDataSource();
            $transaction->begin();
            $error = false;
            if (!empty($photo) && !$this->Image->save($photo))
                $error = true;
            if ($error) {
                $transaction->rollback();
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα',
                    'default', array('class' => Flash::Error));
            } else {
                $transaction->commit();
                $this->Session->setFlash('Η εικόνα προστέθηκε',
                    'default', array('class' => Flash::Success));
                $this->redirect(array(
                    'controller' => 'offers', 'action' => 'imageedit', $id));
            }
        }
    }

    private function upload_error($id, $error) {
        switch ($error) {
            case 'empty':
                $error_msg = 'Παρακαλώ επιλέξτε εικόνα';
                break;
            case 'size':
                $error_msg = 'Υπερβολικά μεγάλο μέγεθος εικόνας, η εικόνα δεν αποθηκεύτηκε';
                break;
            case 'filetype':
                $error_msg = 'Επιτρέπονται μόνο αρχεία PNG, GIF και JPG';
                break;
            default:
                return false;
        }

        $this->Session->setFlash($error_msg,
            'default', array('class' => Flash::Error));
        $this->redirect(array(
            'controller' => 'offers', 'action' => 'imageedit', $id));
    }

    private function valid_type($file) {
        // check if uploaded image has a valid filetype
        $valid_types = array('png', 'jpg', 'jpeg', 'gif');

        if (in_array($this->Common->upload_file_type($file), $valid_types)) {
            return true;
        }
        return false;
    }

    // Wrapper functions of `_change_state' for the activation of an offer
    // specifying a redirect target.
    //
    // @param $id offer id to activate
    public function activate_from_company($id = null) {
        $this->_change_state($id, array(
            'controller' => 'companies',
            'action' => 'view'));
    }
    public function activate_from_offer($id = null) {
        $this->_change_state($id, array(
            'controller' => 'offers',
            'action' => 'view', $id));
    }

    // Wrapper functions of `_change_state' for the termination of an offer
    // specifying a redirect target.
    //
    // @param $id offer id to terminate
    public function terminate_from_company($id = null) {
        $this->_change_state($id, array(
            'controller' => 'companies',
            'action' => 'view'), true);
    }
    public function terminate_from_offer($id = null) {
        $this->_change_state($id, array(
            'controller' => 'offers',
            'action' => 'view', $id), true);
    }
    // this will (potentially) be used in the administrative page of all offers
#    public function terminate_from_admin($id = null) {
#    }

    // Responsible for manipulating the state of an offer.
    //
    // @param $id the offer to activate/terminate
    // @param $redirect passed into $this->redirect; if omitted,no redirection
    //      will take place
    // @param $should_terminate determines if offer should be activated or
    //      terminated; defaults to false, resulting in its activation
    // @throws ForbiddenException if necessary conditions for
    //      activation/termination are not met
    private function _change_state($id = null, $redirect = null, $should_terminate = false) {
        $error = false;
        $status = 200;
        $class = Flash::Success;
        if ($should_terminate) {
            if ($this->Offer->terminate($id)) {
                $msg = 'Η προσφορά τερματίστηκε';
            } else {
                $error = true;
            }
        } else {
            if ($this->Offer->activate($id)) {
                $msg = 'Η προσφορά ενεργοποιήθηκε';
            } else {
                $error = true;
            }
        }

        // this is unlinkely to occur
        if ($error) {
            $msg = 'Προέκυψε κάποιο σφάλμα';
            $status = 400;
            $class = Flash::Error;
        }

        $this->notify(
            array($msg, 'default', array('class' => $class)),
            array($redirect),
            $status);
    }

    private function get_time($time) {
        $ts = strtotime($time);
        $h = date('H', $ts);
        $m = date('i', $ts);
        return array('hour' => $h, 'min' => $m);
    }

    private function trim_time($time) {
        return substr($time, 0, -3);
    }

    public function delete($id = null) {
        // An Offer can be delete only if it's draft.
        // At first, attempt to delete all Images and WorkHours
        // related to this Offer and then delete Offer.

        $options['conditions'] = array('Offer.id' => $id);
        $offer = $this->Offer->find('first', $options);

        if ($offer['Offer']['offer_state_id'] == STATE_DRAFT) {
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

                return $this->notify(
                    // `setFlash' params
                    array(  'Παρουσιάστηκε κάποιο σφάλμα',
                             'default',
                             array('class' => Flash::Error)),
                    // `redirect' params
                    array(  array(  'controller' => 'offers',
                                    'action' => 'view',
                                    $offer['Offer']['id'])),
                    // webservice api call status and extra info
                    400,
                    array(  'id' => $id));

            } else {
                $transaction->commit();

                return $this->notify(
                    array(  'Η προσφορά διαγράφηκε επιτυχώς',
                             'default',
                             array('class' => Flash::Success)),
                    array(  array(  'controller' => 'companies',
                                    'action' => 'view',
                                    $offer['Company']['id'])),
                    200,
                    array(  'id' => $id));
            }
        } else {

            return $this->notify(
                array(  'Η προσφορά δεν μπορεί να διαγραφεί',
                         'default',
                         array('class' => Flash::Info)),
                array(  array(  'controller' => 'offers',
                                'action' => 'view',
                                $offer['Offer']['id'])),
                400,
                array(  'id' => $id));
        }
    }

    // Transforms an array of offers in CakePHP's intrinsic format into an array
    // capable of been converted into either XML or JSON.
    // Handles individual offer data as well as that of multiple offers.
    //
    // Currently, if `Offer' key is present at root level, then it is presumed
    // that the Retrieve operation was activated; otherwise, indexing is
    // presumed.
    //
    // @param $data must contain `Offer', `WorkHour' (ie, offer hours) and
    //      `Company' on case of a retrieve operation; an array of such
    //      sub-arrays for each 'row', otherwise
    // @param $is_xml defines whether the array should be formatted with xml in
    //      mind; default to true
    // @returns an array to be rendered as xml or json
    private function api_prepare_view($data, $is_xml = true) {

        // the result to be rendered in xml or json format; it is what this
        // function returns
        $result = array();

        $is_index = !array_key_exists('Offer', $data);

        // upon execution of the succeeding block, $offer_data becomes a
        // 0-based-index array of offers; this permits a more uniform handling
        // of iterating
        if ($is_index) {

            // the `_path' variables determine where should an item be inserted
            // into the $result array;
            // it is used to implement the appropriate array format
            $offer_path = 'offers.offer.';
            $company_path = 'companies.company.';

            // allows offers coming from differect operations (most notably
            // retrieve and index) to be handled in a uniform fashion, ie as an
            // array of 0-based-index arrays of offers (note that references are
            // used in order to minimize overhead)
            $offer_data = &$data;

        } else {

            $offer_path = 'offer.';
            $company_path = 'company.';

            $offer_data = array(0 => &$data);
        }

        // affects what details make sense to be returned (ie, not set to null)
        $uid = $this->Auth->user('id');

        // use this format unless one of the predefined constants is preferred
        $date_format = 'Y-m-d\TH:i:s';

        // the ids of companies that have already been formatted (avoid using
        // `in_array')
        $companies_id = array();

        // counters; utilized by `Set' to insert the current offer/company
        $offer_i = 0;
        $company_i = 0;
        foreach ($offer_data as $offer) {

            // -- make all necessary alterations --

            // identify logged in user as owner of company, and thus, offer as
            // well
            $is_owner = $uid == $offer['Company']['user_id'];

            // `r' is a temporary vessel of `Offer'
            $r = &$offer['Offer'];
            $company_id = $r['company_id'];

            // not needed
            unset($r['work_hour_count']);

            if (!$is_owner) {
                $r['autostart'] = null;
                $r['autoend'] = null;
            }

            // null-ify certain tags that wouldn't make sense for all offer
            // types
            if ($r['offer_type_id'] != TYPE_COUPONS) {
                $r['coupon_terms'] = null;
                $r['total_quantity'] = null;
                $r['max_per_student'] = null;
            }

            unset($r['offer_category_id']);
            $r['offer_category'] = $offer['OfferCategory']['name'];

            unset($r['offer_type_id']);
            $r['offer_type'] = $offer['OfferType']['name'];

            unset($r['offer_state_id']);
            $r['offer_state'] = $offer['OfferState']['name'];


            // work hours (will be displayed as `offer_hours')
            $r['offer_hours'] = array();
            foreach ($offer['WorkHour'] as $wh) {
                unset($wh['id']);
                unset($wh['company_id']);
                unset($wh['offer_id']);
                $r['offer_hours'][] = $wh;
            }

            // -- perform insertions --
            $result = Set::insert($result, $offer_path.$offer_i, $r);

            if (!array_key_exists($company_id, $companies_id)) {

                // store current offer's company id for future reference
                $companies_id[$company_id] = null;

                // remember: $offer is the current record of the offers array
                $company = &$offer['Company'];
                unset($company['work_hour_count']);

                $result = Set::insert(
                    $result, $company_path.$company_i, $company);

                ++$company_i;
            }

            ++$offer_i;
        }

        // make any additional alterations, specifically for xml format
        if ($is_xml) {

            // create a simple wrapper of entities to be formatted
            // in case of Index operation, just omit the enclosing `offers' and
            // `companies' tags
            if ($is_index) {
                $wrap = array(
                    'offer' => &$result['offers']['offer'],
                    'company' => &$result['companies']['company']);
            } else {
                $wrap = &$result;
            }

            $this->xml_alter_view($wrap,$date_format);
        } else {
            // remove redundant index when requesting just one offer
            if ($is_index) {
                $result['offers'] = &$result['offers']['offer'];
                $result['companies'] = &$result['companies']['company'];
            } else {
                $result['offer'] = &$result['offer'][0];
                $result['company'] = &$result['company'][0];
            }
        }

        return $result;
    }

    // Makes necessary modifications to the supplied array `data' so that it may
    // properly be rendered in XML format. Currently, it is assumed that each
    // element possess an `id' field and several date fields. The latter are
    // defined inside the function.
    //
    // @param $data array containing `'offer' and `company' sub-arrays. Note
    //      that this param is passed by reference
    // @param $date_format a string or predefined date format to apply to all
    //      dates
    private function xml_alter_view(&$data, $date_format) {

        // all the date fields that are to be formatted
        $date_fields = array(
            'offer' => array(
                'started',
                'ended',
                'autostart',
                'autoend',
                'created',
                'modified'),
            'company' => array(
                'created',
                'modified'));

        // it is assumed that all entities possess an `id' attribute and,
        // potentially, dates; if not, a different approach is due
        foreach ($data as $type => $entities) {

            foreach ($entities as $index => $entity) {

                // make offer id appear as attribute
                $entity['@id'] = $entity['id'];
                unset($entity['id']);

                // format dates for this entity's date fields
                foreach ($date_fields[$type] as $field) {

                    // get entity's date from field $field
                    $date = $entity[$field];
                    if (!empty($date)) {
                        // format date
                        $entity[$field] = date($date_format, strtotime($date));
                    }
                }

                // insert updated offer back to the results
                $data[$type][$index] = $entity;
            }
        }
    }
}
