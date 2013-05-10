<?php

App::uses('CakeEmail', 'Network/Email');

class Offer extends AppModel {
    public $name = 'Offer';
    public $belongsTo = array('Company', 'OfferCategory');
    public $hasMany = array('Coupon', 'Image', 'WorkHour');
    public $findMethods = array(
        'valid' => true,
        'happyhour' => true,
        'coupons' => true,
        'limited' => true,
        'search' => true,
        'tag' => true,
        'typeStats' => true,
        'couponInfo' => true,
    );
    public $virtualFields = array(
        'vote_sum' => 'Offer.vote_plus - Offer.vote_minus');

    public $conditionsValid = array(
            'Offer.offer_state_id' => STATE_ACTIVE,
            'Offer.is_spam' => 0,
            'Company.is_enabled' => 1);

    // Find methods core processing
    private function process_find(&$query) {
        if (isset($query['show_spam'])) {

            unset($query['show_spam']);
            $query['conditions'] = array_merge($query['conditions'],
                                               array('Offer.is_spam' => true));
        } else {

            $query['conditions'] = array_merge(
                $query['conditions'], $this->conditionsValid);
        }

        if (isset($query['order'])) {
            // Handle distance ordering
            if (isset($query['radius'])) {
                $query['order'] = array('Distance.distance' => 'asc');
                $query['group'] = array('Offer.id');
                $query['conditions'] = array_merge(
                    $query['conditions'],
                    array('Distance.distance <=' => $query['radius']));
                $query['joins'] = array(array(
                    'table' => 'distances',
                    'alias' => 'Distance',
                    'type' => 'LEFT',
                    'conditions' => array(
                        'Offer.company_id = Distance.company_id')));
            }
        } else {
            $query['order'] = array('Offer.modified' => 'desc');
        }
    }

    protected function _findCount($state, $query, $results = array()) {
        if ($state === 'before') {
            // prevent count at save from crashing
            if (!isset($query['recursive']) || ($query['recursive'] > -1)) {
                $this->process_find($query);
            }
        }

        return parent::_findCount($state, $query, $results);
    }

    // 'valid' custom find type
    // returns active offers from enabled companies, not spam
    protected function _findValid($state, $query, $results = array()) {
        if ($state === 'before') {
            $this->process_find($query);
            return $query;
        }
        return $results;
    }

    // custom find types for the 3 main offer types
    //      * happyhour
    //      * coupon
    //      * limited
    //
    // These types are not very configurable and have a limited
    // number of duplicate lines *sic*. This way the method calls
    // are very simple without the need for (many) extra parameters
    // eg: $this->Offer->find('coupon');

    // return all valid happy hour type offers
    protected function _findHappyhour($state, $query, $results = array()) {
        if ($state === 'before') {
            $query['conditions'] = array(
                'Offer.offer_type_id' => TYPE_HAPPYHOUR,);
            $this->process_find($query);
            return $query;
        }
        return $results;
    }

    // return all valid coupon type offers
    protected function _findCoupons($state, $query, $results = array()) {
        if ($state === 'before') {
            $query['conditions'] = array(
                'Offer.offer_type_id' => TYPE_COUPONS,);
            $this->process_find($query);
            return $query;
        }
        return $results;
    }

    // return all valid limited type offers
    protected function _findLimited($state, $query, $results = array()) {
        if ($state === 'before') {
            $query['conditions'] = array(
                'Offer.offer_type_id' => TYPE_LIMITED,);
            $this->process_find($query);
            return $query;
        }
        return $results;
    }

    // find offers based on keywords
    // searches in `tags` field in offers table
    //
    // on call accepts an array of the form:
    //      array('tag' => 'tagname')
    // eg:
    //      $this->Offer->find('tag', array('tag' => 'tagname'));
    protected function _findTag($state, $query, $results = array()) {
        if ($state === 'before') {
            $query['conditions'] = array(
                'Offer.tags LIKE' => "%{$query['tag']}%",);
            $this->process_find($query);
            return $query;
        }
        return $results;
    }

    // find offers based on keywords
    // searches in title, description, tags fields in offers table
    // and name in companies table
    protected function _findSearch($state, $query, $results = array()) {
        if ($state === 'before') {
            $conditions = array();

            if (isset($query['words'])) {
                foreach ($query['words'] as $word) {
                    $condition[] = array('Offer.title LIKE' => "%{$word}%");
                    $condition[] = array('Offer.description LIKE' => "%{$word}%");
                    $condition[] = array('Offer.tags LIKE' => "%{$word}%");
                    $condition[] = array('Company.name LIKE' => "%{$word}%");
                }
                $conditions['OR'] = $condition;
            }

            $query['conditions'] = array_merge($query['conditions'], $conditions);

            $this->process_find($query);
            return $query;
        }
        return $results;
    }

    protected function _findTypeStats($state, $query, $result = array()) {
        if ($state === 'before') {
            $query['fields'] = array('Offer.offer_type_id', 'COUNT(*) as offer_count');
            $query['group'] = 'Offer.offer_type_id';
            $query['conditions'] = array();

            $this->process_find($query);
            return $query;
        }

        // create an array so as to iterate through the count results, and make
        // sure that all 'count' fields are initialized to 0 just in case there
        // are no offers of a particular type
        $types = array(
            TYPE_HAPPYHOUR => array('id' => TYPE_HAPPYHOUR,
                                    'name' => offer_type(TYPE_HAPPYHOUR),
                                    'offer_count' => 0),
            TYPE_COUPONS => array('id' => TYPE_COUPONS,
                                  'name' => offer_type(TYPE_COUPONS),
                                  'offer_count' => 0),
            TYPE_LIMITED => array('id' => TYPE_LIMITED,
                                  'name' => offer_type(TYPE_LIMITED),
                                  'offer_count' => 0));

        foreach ($result as $record) {
            $type_id = $record['Offer']['offer_type_id'];
            $offer_count = $record[0]['offer_count'];

            $types[$type_id]['offer_count'] = $offer_count;
        }

        return array_values($types);
    }

    // Capable of returning info regarding Coupon offers that have been
    // terminated normally (i.e. without being flagged as 'improper').
    // The supported info can be any field of the following tables: Offer,
    // Company, User; one need only specify which fields are to be returned by
    // providing an appropriate 'fields' array, for example:
    //  'fields' => array('Offer.title',
    //                    'Offer.offer_category_id',
    //                    'Company.name',
    //                    'User.email')
    // Additional tables can be made available by specifying a 'joins'
    // parameter. Please do note, though, that the table aliases 'Company' and
    // 'User' are already defined internally and one should not use them.
    protected function _findCouponInfo($state, $query = array(), $result = array()) {
        if ($state === 'before') {
            $options = array('conditions' => array(
                                 'Offer.offer_type_id' => TYPE_COUPONS,
                                 'Offer.is_spam' => false,
                                 'Offer.offer_state_id' => STATE_INACTIVE),
                             'joins' => array(
                                 array('table' => 'companies',
                                       'alias' => 'Company',
                                       'type' => 'LEFT',
                                       'conditions' => array(
                                           'Offer.company_id = Company.id',
                                       )),
                                 array('table' => 'users',
                                       'alias' => 'User',
                                       'type' => 'LEFT',
                                       'conditions' => array(
                                           'Company.user_id = User.id',
                                       ))));
            $query = array_merge_recursive($query, $options);
            return $query;
        }
        return $result;
    }

    // @param $company_id limits find to offers that belong to the specified
    // company
    // return an array with keys: `draft', `active', `inactive', each of which
    // corresponds to an array of offers
    public function find_all($company_id = NULL, $shows_spam = false) {
        $group = '/Offer[offer_state_id=';
        $options = array();
        if(!empty($company_id)) {
            $options['conditions'] = array('Offer.company_id' => $company_id);
        }
        if(!$shows_spam) {
            $options['conditions'][] = array('Offer.is_spam' => NOT_SPAM);
        }

        $options['fields'] = array('Offer.*');
        $options['order'] = array('Offer.created ASC');
        $options['recursive'] = 0;

        $result = $this->find('all', $options);
        $offers = array();

        // using Set:: extract, for now
        $offers['Draft'] = Set::extract($group.STATE_DRAFT.']/.', $result);
        $offers['Active'] = Set::extract($group.STATE_ACTIVE.']/.', $result);
        $offers['Inactive'] = Set::extract($group.STATE_INACTIVE.']/.', $result);

        return $offers;
    }

    // TODO: this function could be merged with Offer::terminate into one
    // Manually activate a specific offer. Authorization must be ensured
    // beforehand.
    // @param $id id of offer to activate
    // @throws ForbiddenException when activate conditions are not met
    public function activate($id = null) {
        // fetch only the fields required to be checked and updated
        $this->read(array('offer_state_id'), $id);

        // only active offers may be terminated
        if ($this->data['Offer']['offer_state_id'] == STATE_DRAFT) {

            $this->set('offer_state_id', STATE_ACTIVE);
            $this->set('started', date('Y-m-d H:i:s'));

            return $this->save(null, false) != false;
        } else {
            throw new ForbiddenException('Η προσφορά δεν δύναται ενεργοποίησης.');
        }
    }
    // TODO: this function could be merged with Offer::activate into one
    // Manually terminate a specific offer. Authorization must be ensured
    // beforehand.
    // @param $id id of offer to terminate
    // @throws ForbiddenException when termination conditions are not met
    public function terminate($id = null) {
        // fetch only the fields required to be checked and updated
        $this->read(array('offer_state_id'), $id);

        // only active offers may be terminated
        if ($this->data['Offer']['offer_state_id'] == STATE_ACTIVE) {
            // update state and manual end date
            $data = array(
                'offer_state_id' => STATE_INACTIVE,
                'ended' => date('Y-m-d H:i:s')
            );
            return $this->save($data, false) != false;
        } else {
            throw new ForbiddenException('Η προσφορά δεν δύναται τερματισμού.');
        }
    }

    // rules that apply at all times
    public $validate = array(

        'title' => array(
            'not_empty' => array(
                'rule' => 'notEmpty',
                'message' => 'Ο τίτλος δεν μπορεί να παραμείνει κενός.',
                'required' => true,
                // if was left empty or not even included, it's redundant to
                // check whether the (non-existent) value is valid or not
                'last' => true,
                // it should not be required for an update
                'on' => 'create'
            ),
            // this rule ensures that if the title key was specified, although
            // not mandatory for an update, its value should not be empty
            // only reason for implementing this is to present a more appopriate
            // error message
            'not_empty_on_update' => array(
                'rule' => 'notEmpty',
                'required' => false,
                'message' => 'Ο τίτλος δεν μπορεί να παραμείνει κενός.',
                'last' => true,
                'on' => 'update'
            ),
            'valid' => array(
                'rule' => '/^[\w\dαβγδεζηθικλμνξοπρστυφχψωΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩΆάΈέΎΉήύΊίΌόΏώϊϋΐΰς\-,. &%€]+$/',
                'allowEmpty' => false,
                'message' => 'Ο τίτλος περιέχει μη έγκυρους χαρακτήρες.'
            ),
        ),

        'description' => array(
            'not_empty' => array(
                'rule' => 'notEmpty',
                'message' => 'Η περιγραφή δεν μπορεί να παραμείνει κενή.',
                'required' => true,
                'last' => true,
                'on' => 'create'
            ),

            // this rule ensures that if the description key was specified,
            // although not mandatory for an update, its value should not be
            // left empty
            // generally, such check is only required when no (more specific)
            // rules (eg is_numeric) are applied
            'not_empty_on_update' => array(
                'rule' => 'notEmpty',
                'message' => 'Η περιγραφή δεν μπορεί να παραμείνει κενή.',
                'required' => false,
                'last' => true,
                'on' => 'update'
            ),
        ),

        'tags' => array(
            'valid' => array(
                'rule' => '/^[\w\dαβγδεζηθικλμνξοπρστυφχψωΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩΆάΈέΎΉήύΊίΌόΏώϊϋΐΰς\- _]+$/',
                'allowEmpty' => true,
                'message' => 'Οι λέξεις-κλειδιά περιέχουν μη έγκυρους χαρακτήρες.'
            ),
        ),

        'total_quantity' => array(
            'not_empty' => array(
                'rule' => 'notEmpty',
                'required' => true,
                'message' => 'Συμπληρώστε τον αριθμό διαθέσιμων κουπονιών.',
                'last' => true,
                'on' => 'create',
            ),
            'integer' => array(
                'rule' => '/^\d+$/',
                'message' => 'Παρακαλώ εισάγετε ένα θετικό ακέραιο αριθμό.',
                'last' => true
            ),
            'positive' => array(
                'rule' => array('comparison', '>', 0),
                'message' => 'Ο αριθμός πρέπει να είναι θετικός.',
            )
        ),

        'max_per_student' => array(
            'not_empty' => array(
                'rule' => 'notEmpty',
                'required' => true,
                'message' => 'Παρακαλώ εισάγετε μια τιμή.',
                'last' => true,
                'on' => 'create',
            ),
            'integer' => array(
                'rule' => '/^\d+$/',
                 'message' => 'Ο αριθμός πρέπει να είναι θετικός ή μηδέν.',
             ),
            'select' => array(
                'rule' => array('inList', array('0', '1', '2', '3', '5', '10')),
                 'message' => 'Επιλέξτε μία από τις προκαθορισμένες τιμές.',
             )
         ),

        'autostart' => array(
            'valid_date' => array(
                'rule' => 'datetime',
                'allowEmpty' => true,
                'message' => 'Εισάγετε μία έγκυρη ημερομηνία έναρξης.',
            ),
        ),

        'autoend' => array(
            'valid_date' => array(
                'rule' => 'datetime',
                'allowEmpty' => true,
                'message' => 'Εισάγετε μία έγκυρη ημερομηνία λήξης.',
            ),
        ),

        'explanation' => array(
            'rule' => 'empty_explanation',
            'message' => 'Η αιτιολογία δεν μπορεί να παραμείνει κενή.',
        ),
    );

    public function empty_explanation($check) {
        $empty_expl = isset($this->data['Offer']['explanation'])
            ?!empty($this->data['Offer']['explanation'])
            :false;

        return $empty_expl;
    }

    public function is_owned_by($offer_id, $user_id) {
        $company_id = $this->Company->field('id', array('user_id' => $user_id));
        $offer_company_id = $this->field('company_id', array('id' => $offer_id));
        return $company_id === $offer_company_id && $company_id !== false;
    }

    public function flag_improper($id, $explanation) {
        $this->id = $id;
        $data = array(
            'is_spam' => 1,
            'explanation' => $explanation,
            'offer_state_id' => STATE_INACTIVE);

        if ($this->save($data, false)) {
            $flash['msg'] = _('Η προσφορά χαρακτηρίστηκε ως ανάρμοστη');
            $flash['type'] = 'success';
        } else {
            $flash['msg'] = _('Προέκυψε κάποιο σφάλμα. Οι αλλαγές δεν πραγματοποιήθηκαν');
            $flash['type'] = 'error';
        }

        return $flash;
    }

    public function get_company_email($offer_id) {
        $company_id = $this->field('company_id', array('id' => $offer_id));
        $user_id = $this->Company->field('user_id', array('id' => $company_id));
        $email = $this->Company->User->field('email', array('id' => $user_id));

        return $email;
    }

    // returns the emails on students that hold possession of at least coupon of
    // the specified offer
    //
    // the result is an array of: [User][email]
    public function get_student_emails($offer_id) {
        $this->Coupon->recursive = -1;

        $options = array('conditions' => array(
                             'Coupon.offer_id' => $offer_id),
                         'joins' => array(
                             array('table' => 'students',
                                   'alias' => 'Student',
                                   'type' => 'LEFT',
                                   'conditions' => array(
                                       'Coupon.student_id = Student.id',
                                   )),
                             array('table' => 'users',
                                   'alias' => 'User',
                                   'type' => 'LEFT',
                                   'conditions' => array(
                                       'User.id = Student.id',
                                   ))),
                         'fields' => array('DISTINCT User.email'));

        return $this->Coupon->find('all', $options);
    }

    // Iterates through the supplied array, reducing the length of each Offer's
    // description to  $limit.
    //
    // @param &$array An array of Offer records
    // @param $limit The maximum number of characters that each description will
    //      carry
    public function minify_desc(&$array, $limit) {
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

    // Sends an e-mail, listing all coupons of the offer that corresponds to the
    // supplied id to its owner-company.
    //
    // $id - offer id
    public function email_coupon_list($offer_id) {

        // we require the email address to send the coupon listing to, as well
        // as the title of the offer (to use as email subject)
        // also, we need to make sure the offer is indeed of type 'coupons'
        $options = array('conditions' => array('Offer.id' => $offer_id),
                         'fields' => array('Offer.title',
                                           'User.email'));

        $this->recursive = -1;
        // custom finders run as 'findAll' and not 'findFirst' so to access the
        // actual data, one must specify an index (in this case: 0)
        $offer = $this->find('couponInfo', $options);

        // fetch any available coupons
        if (! empty($offer)) {
            $coupons = $this->Coupon->get_offer_coupons($offer_id);

            if (! empty($coupons)) {

                $offer_title = $offer[0]['Offer']['title'];
                $owner_email = $offer[0]['User']['email'];

                $email = new CakeEmail('default');
                // set parameters that are the same for all emails to be sent
                $email = $email
                    ->to($owner_email)
                    ->subject(__("Κουπόνια προσφοράς «{$offer_title}»"))
                    ->template('company_coupons', 'default')
                    ->emailFormat('both')
                    ->viewVars(array(
                        'offer_title' => $offer_title,
                        'coupons' => $coupons));

                try {
                    $email->send();
                } catch(Exception $e) {}
            }
        }
    }

}
