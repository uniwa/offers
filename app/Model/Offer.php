<?php

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
        'tag' => true
    );
    public $virtualFields = array(
        'vote_sum' => 'Offer.vote_plus - Offer.vote_minus');

    public $conditionsValid = array(
            'Offer.offer_state_id' => STATE_ACTIVE,
            'Offer.is_spam' => 0,
            'Company.is_enabled' => 1);

    // Find methods core processing
    private function process_find(&$query) {
        $query['conditions'] = array_merge($query['conditions'], array(
            'Offer.offer_state_id' => STATE_ACTIVE,
            'Offer.is_spam' => 0,
            'Company.is_enabled' => 1));

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
            $this->process_find($query);
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
    protected function _findSearch($state, $query, $results = array()) {
        if ($state === 'before') {
            $conditions = array();
            foreach ($query['words'] as $word) {
                $condition[] = array('Offer.title LIKE' => "%{$word}%");
                $condition[] = array('Offer.description LIKE' => "%{$word}%");
                $condition[] = array('Offer.tags LIKE' => "%{$word}%");
            }
            $conditions['OR'] = $condition;
            $query['conditions'] = $conditions;
            $this->process_find($query);
            return $query;
        }
        return $results;
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
                'rule' => '/^[\w\dαβγδεζηθικλμνξοπρστυφχψωΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩΆάΈέΎΉήύΊίΌόΏώϊϋΐΰς\-,. &]+$/',
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
    );

    public function is_owned_by($offer_id, $user_id) {
        $company_id = $this->Company->field('id', array('user_id' => $user_id));
        $offer_company_id = $this->field('company_id', array('id' => $offer_id));
        return $company_id === $offer_company_id && $company_id !== false;
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

}
