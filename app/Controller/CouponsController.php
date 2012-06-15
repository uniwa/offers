<?php

//TODO: maybe place this elsewhere?
App::uses('CakeEmail', 'Network/Email');

class CouponsController extends AppController {

    public $name = 'Coupons';
    public $uses = array('Coupon', 'Offer');
    public $helpers = array('Html', 'Time');
    public $components = array('RequestHandler');

    public function beforeFilter() {
        $this->api_initialize();

        if (! $this->is_authorized($this->Auth->user()))
            throw new ForbiddenException();

        parent::beforeFilter();
    }

    public function add ($id = null) {
        if ($id === null)
            throw new BadRequestException();

        $redirect = array($this->referer());

        // check if offer exists and is valid
        $conditions = array('Offer.id' => $id);
        $offer = $this->Offer->find('valid',
                                    array('conditions' => $conditions));
        if (! $offer)
            throw new NotFoundException('Η προσφορά δεν βρέθηκε.');

        // don't read from session all the time
        $student_id = $this->Session->read('Auth.Student.id');

        // check if user is allowed to get the coupon due to maximum
        // coupon number acquired
        if ($this->Coupon->max_coupons_reached($id, $student_id)) {

            $flash = array('Έχετε δεσμεύσει τον μέγιστο αριθμό κουπονιών για '
                           .'αυτήν την προσφορά.',
                           'default',
                           array('class' => Flash::Error));

            return $this->notify($flash, $redirect, 400);
        }

        // create a unique id
        $coupon_uuid = $this->generate_uuid();
        $coupon['Coupon']['serial_number'] = $coupon_uuid;

        $coupon['Coupon']['is_used'] = 0;
        $coupon['Coupon']['student_id'] = $student_id;
        $coupon['Coupon']['offer_id'] = $id;

        if ($this->Coupon->save($coupon)) {

            $coupon_id = $this->Coupon->id;

            // this could have been done above to avoid a second query, but is
            // containable worth it?
            $this->Offer->Behaviors->attach('Containable');
            $this->Offer->contain(array('Company.Municipality.County'));
            $res = $this->Offer->findById($id);

            // send email
            $this->mail_success($res, $coupon_id, $coupon_uuid);


            // success getting coupon
            // differentiate responses based on Accept header parameter
            $flash = array('Το κουπόνι δεσμεύτηκε επιτυχώς',
                           'default',
                           array('class' => Flash::Success));

            $status = 200;
            $extra = array('id' => $coupon_id,
                           'serial_number' => $coupon_uuid);

        }
        else {
            // error getting coupon
            // differentiate responses based on Accept header parameter
            $flash = array('Παρουσιάστηκε κάποιο σφάλμα',
                           'default',
                           array('class' => Flash::Error));
            $status = 400;

            $extra = array();
        }

        $this->notify($flash, $redirect, $status, $extra);
    }

    public function view($id = null) {
        if ($id === null)
            throw new BadRequestException();

        // fetch coupon and all associated data
        //
        // sample $coupon array:
        //      'Coupon'
        //      'Offer'
        //          `-'Company'
        //      'Student'
        $cond = array('Coupon.id' => $id);

        $this->Coupon->Behaviors->attach('Containable');
        $this->Coupon->contain(array('Offer.Company', 'Student'));
        $coupon = $this->Coupon->find('first', array('conditions' => $cond));

        if (! $coupon)
            throw new NotFoundException();

        if ($coupon['Coupon']['student_id'] !==
            $this->Session->read('Auth.Student.id'))
            throw new ForbiddenException();

        if ($coupon['Offer']['is_spam'])
            throw new ForbiddenException('Η προσφορά για την οποία έχει'
                .' δεσμευθεί το κουπόνι σας έχει χαρακτηριστεί σαν SPAM.');

        if ($this->is_webservice) {
            switch ($this->webservice_type) {
                case 'js':
                case 'json':
                    $coupon = $this->api_prepare_view($coupon, false);
                    break;

                case 'xml':
                    $coupon = $this->api_prepare_view($coupon);
                    break;
            }

            $this->api_compile_response(200, $coupon);
        } else {
            $this->set('coupon', $coupon);
        }
    }

    private function api_prepare_view($data, $is_xml = true) {
        $is_index = !array_key_exists('Coupon', $data);

        if (! $is_index) {
            $data = array(0 => $data);
        }

        // fields we don't want in results
        $unset_r = array(
            'offer' => array('created', 'modified'),
            'coupon' => array('modified', 'is_used'),
            'student' => array(
                'receive_email',
                'token',
                'created',
                'modified',
                'image_id'),
            'company' => array(
                'is_enabled',
                'user_id',
                // municipalities are not returned at all
                // enable them in find query and then remove the following line
                'municipality_id',
                'image_count',
                'work_hour_count',
                'created',
                'modified'
            )
        );

        $api_data = array();
        foreach ($data as $d) {
            $coupon_data = array();
            // format return data
            $coupon_data['offer'] = $d['Offer'];

            if (isset($coupon_data['offer']['Company'])) {
                unset($coupon_data['offer']['Company']);
            }

            $coupon_data['coupon'] = $d['Coupon'];

            if (isset($d['Student'])) {
                $coupon_data['student'] = $d['Student'];
            }

            if (isset($d['Company'])) {
                $coupon_data['company'] = $d['Offer']['Company'];
            }

            foreach ($coupon_data as $key => $val) {
                foreach ($val as $skey => $sval) {
                    if (in_array($skey, $unset_r[$key])) {
                        unset($coupon_data[$key][$skey]);
                    }
                }
            }

            if ($is_xml) {
                $this->xml_alter_view($coupon_data);
            }
            $api_data[] = $coupon_data;
        }

        if (! $is_index) {
            $api_data = $api_data[0];
        } else {
            $api_data = array('coupons' => $api_data);
        }

        return $api_data;
    }

    private function xml_alter_view(&$data, $date_format='Y-m-d\TH:i:s') {

        // all the date fields that are to be formatted
        $date_fields = array(
            'offer' => array(
                'started',
                'ended',
                'autostart',
                'autoend'),
            'coupon' => array(
                'created'));

        // it is assumed that all entities possess an `id' attribute and,
        // potentially, dates; if not, a different approach is due
        foreach ($data as $key => $val) {
            // $key -> 'offer'
            // $val -> array contents of 'offer' key
            if (empty($val)) continue;

            // make offer id appear as attribute
            if (isset($val['id'])) {
                $val['@id'] = $val['id'];
                unset($val['id']);
            }

            // if array data key, has dates
            if (array_key_exists($key, $date_fields)) {
                // iterate over possible date fields for key
                foreach ($date_fields[$key] as $field) {
                    // get val's date from field $field
                    if (array_key_exists($field, $val)) {
                        // format date
                        $val[$field] = date($date_format, strtotime($val[$field]));
                    }
                }
            }

            // insert updated offer back to the results
            $data[$key] = $val;
        }
    }

    public function index() {
        $student_id = $this->Session->read('Auth.Student.id');
        if (! $student_id) throw new ForbiddenException();

        $cond = array('Coupon.student_id' => $student_id);
        $order = array('Coupon.created DESC');
        // fetch specific fields
        $fields = array(
            'Coupon.id',
            'Coupon.serial_number',
            'Coupon.created',
            'Offer.title',
            'Offer.description',
            'Offer.coupon_terms',
            'Offer.offer_category_id',
            'Offer.offer_type_id',
            'Offer.vote_count',
            'Offer.vote_sum',
            'Offer.company_id',
        );
        $this->Coupon->recursive = 0;

        $coupons = $this->Coupon->find('all', array(
            'conditions' => $cond,
            'fields' => $fields,
            'order' => $order)
        );

        if ($this->is_webservice) {
            switch ($this->webservice_type) {
                case 'js':
                case 'json':
                    $coupons = $this->api_prepare_view($coupons, false);
                    break;

                case 'xml':
                    $coupons = $this->api_prepare_view($coupons);
                    break;
            }

            $this->api_compile_response(200, $coupons);
        } else {
            throw new NotFoundException();
        }
    }

    public function delete($id = null) {
        // get offer id
        $offer_id = $this->Coupon->field('offer_id', array(
            'id' => $id));

        // check if offer has ended
        $has_ended = $this->Offer->field('ended', array(
            'id' => $offer_id));

        if (! $has_ended) {
            throw new ForbiddenException();
        }

        $this->Coupon->id = $id;
        $result = $this->Coupon->saveField('student_id', 
            null, $validate = false);

        if ($result == false) {
            $flash = array('Παρουσιάστηκε ένα σφάλμα κατα την διαγραφή του κουπονιού.',
                'default',
                array('class' => Flash::Error));
            $status = 500;
        } else {
            $flash = array('Το κουπόνι διεγράφη με επιτυχία.',
                'default',
                array('class' => Flash::Success));
            $status = 200;
        }
        $redirect = array($this->referer());
        $this->notify($flash, $redirect, $status);
    }


    private function generate_uuid() {
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

        return $uuid;
    }

    public function is_authorized($user) {
        if ($user['is_banned'] == 0) {
            if (in_array($this->action, array('add', 'view', 'index'))) {
                // only students can get coupons
                if ($user['role'] !== ROLE_STUDENT) {
                    return false;
                }
                return true;
            }
            if ($this->action === 'delete') {
                if ($user['role'] !== ROLE_STUDENT) {
                    return false;
                }

                $student_id = $this->Session->read('Auth.Student.id');
                if ($this->Coupon->is_owned_by($this->request->params['pass'],
                                               $student_id)) {
                    return true;
                }
                return false;
            }
        }

        // admin can see banned users too
        return parent::is_authorized($user);
    }

    private function mail_success($offer, $coupon_id, $coupon_uuid) {
        $student_email = $this->Session->read('Auth.User.email');

        $offer_title = $offer['Offer']['title'];

        $municipality = Set::check($offer, 'Company.Municipality.name') ?
            $offer['Company']['Municipality']['name'] : null;

        // could it be that a company may specify county but not municipality?
        $county = Set::check($offer, 'Company.Municipality.County.name') ?
            $offer['Company']['Municipality']['County']['name'] : null;

        $email = new CakeEmail('default');
        $email = $email
            ->to($student_email)

            ->subject("Κουπόνι προσφοράς «{$offer_title}»")
            ->template('coupon_reservation', 'default')
            ->emailFormat('both')
            ->viewVars(array(
                'offer_id' => $offer['Offer']['id'],
                'offer_title' => $offer_title,
                'coupon_id' => $coupon_id,
                'coupon_uuid' => $coupon_uuid,
                'company' => $offer['Company'],
                'municipality' => $municipality,
                'county' => $county));

        try {
            $email->send();
        } catch (Exception $e) {
            //do what with it?
        }
    }
}
