<?php

class Offer extends AppModel {
    public $name = 'Offer';
    public $belongsTo = array('Company', 'OfferType', 'OfferCategory', 'OfferState');
    public $hasMany = array('Coupon', 'Image', 'WorkHour');
    public $findMethods = array(
        'valid' => true,
        'happyhour' => true,
        'coupons' => true,
        'limited' => true,
        'tag' => true
    );

    // 'valid' custom find type
    // returns active offers from enabled companies, not spam
    protected function _findValid($state, $query, $results = array()) {
	    if ($state === 'before') {
			$query['conditions'] = array_merge($query['conditions'], array(
                'Offer.offer_state_id' => STATE_ACTIVE,
                'Offer.is_spam' => 0,
                'Company.is_enabled' => 1));
			if (!isset($query['order']))
    			$query['order'] = array('Offer.modified' => 'desc');
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
                'Offer.offer_type_id' => TYPE_HAPPYHOUR,
                'Offer.offer_state_id' => STATE_ACTIVE,
                'Offer.is_spam' => 0,
                'Company.is_enabled' => 1
            );
			if (!isset($query['order']))
    			$query['order'] = array('Offer.modified' => 'desc');
            return $query;
        }
        return $results;
    }

    // return all valid coupon type offers
    protected function _findCoupons($state, $query, $results = array()) {
        if ($state === 'before') {
            $query['conditions'] = array(
                'Offer.offer_type_id' => TYPE_COUPONS,
                'Offer.offer_state_id' => STATE_ACTIVE,
                'Offer.is_spam' => 0,
                'Company.is_enabled' => 1
            );
			if (!isset($query['order']))
    			$query['order'] = array('Offer.modified' => 'desc');
            return $query;
        }
        return $results;
    }

    // return all valid limited type offers
    protected function _findLimited($state, $query, $results = array()) {
        if ($state === 'before') {
            $query['conditions'] = array(
                'Offer.offer_type_id' => TYPE_LIMITED,
                'Offer.offer_state_id' => STATE_ACTIVE,
                'Offer.is_spam' => 0,
                'Company.is_enabled' => 1
            );
			if (!isset($query['order']))
    			$query['order'] = array('Offer.modified' => 'desc');
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
                'Offer.tags LIKE' => "%{$query['tag']}%",
                'Offer.offer_state_id' => STATE_ACTIVE,
                'Offer.is_spam' => 0,
                'Company.is_enabled' => 1
            );
			if (!isset($query['order']))
    			$query['order'] = array('Offer.modified' => 'desc');
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

    // This function updates the state of all offers based on their autostart
    // and autoend values.
    // It should be invoked BEFORE any operation that requires up-to-date offer
    // state.
    //
    // @param $company_id limits update operations only to offers that belong to
    // the specified company id
    public function update_state($company_id = NULL) {
        // date by which to compare autostart and autoend dates
        $now = date('Y-m-d H:i:s');

        $this->activate_offers($company_id, $now);
        $this->deactivate_offers($company_id, $now);
    }

    // TODO: this function could be merged with Offer::terminate into one
    // Manually activate a specific offer. Authorization must be ensured
    // beforehand.
    // @param $id id of offer to activate
    // @throws ForbiddenException when activate conditions are not met
    public function activate($id = null) {

        // TODO see if just offer_state_id and ended can be fetched and have
        // this work still
        if (empty($this->data)) {
            // fetch only the fields required to be checked and updated
            $this->recursive = -1;
            $this->read('Offer.*', $id);
        }

        // only active offers may be terminated
        if ($this->data['Offer']['offer_state_id'] == STATE_DRAFT) {

            $this->set('offer_state_id', STATE_ACTIVE);
            $this->set('started', date('Y-m-d H:i:s'));

            return $this->save(null, false);
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
            return $this->save($data, false);
        } else {
            throw new ForbiddenException('Η προσφορά δεν δύναται απενεργοποίησης.');
        }
    }

    private function activate_offers($company_id = NULL, $date) {
        // happy-hour offers do not automatically activate

        // coupon offers activate if current date is later than an offer's
        // autostart date and the available coupons are not yet depleted

        // (time-)limited offers activate if current date is between autostart
        // and autoend dates

            // what about `is_spam' ?
            // is it definite that such offers have already been set to
            // inactive?

        $conditions = array(
            // we bear in mind not to activate any offers that have been
            // manually deactivated (ie, we activate only Draft offers)
            'Offer.offer_state_id' => STATE_DRAFT,
            'OR' => array(
                // conditions to activate a Coupons-type offer
                array(
                    'Offer.offer_type_id' => TYPE_COUPONS,
                    'Offer.autostart <=' => $date,
                    'Offer.coupon_count < Offer.total_quantity'
                ),

                // conditions to activate a (Time-)Limited-type offer
                array(
                    'Offer.offer_type_id' => TYPE_LIMITED,
                    'Offer.autostart <=' => $date,
                    'Offer.autoend >' => $date
                )
            )
        );

        if(!empty($company_id)) {
            array_push($conditions, array('Offer.company_id' => $company_id));
        }

        $this->updateAll(
            array(
                'Offer.offer_state_id' => STATE_ACTIVE,
                'Offer.started' => 'Offer.autostart'
            ),
            $conditions
        );
    }

    private function deactivate_offers($company_id = NULL, $date) {
        // happy-hour offers do not automatically deactivate

        // coupon offers deactivate if no more coupons are available

        // (time-)limited offers deactivate if current date exceeds autoend

        $conditions = array(
            // we bear in mind to deactivate offers that are only currently
            // active, effectively avoiding writing to already inactive ones
            'Offer.offer_state_id' => STATE_ACTIVE,
            'OR' => array(
                // conditions to deactivate a coupons-type offer
                array(
                    'Offer.offer_type_id' => TYPE_COUPONS,
                    'Offer.coupon_count >= Offer.total_quantity'
                ),

                // conditions to deactivate a (time-)limited-type offer
                array(
                    'Offer.offer_type_id' => TYPE_LIMITED,
                    'Offer.autoend <=' => $date
                )
            )
        );
        if(!empty($company_id)) {
            array_push($conditions, array( 'Offer.company_id' => $company_id ));
        }

        $this->updateAll(
            array(
                'Offer.offer_state_id' => STATE_INACTIVE,
                'Offer.ended' => 'Offer.autoend'
            ),
            $conditions
        );
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
    );

    // @Override
    // The purpose of overriding this method is to provide pre-validation
    // data preparation.
    public function beforeValidate($options = array()) {

        // an empty `offer_type_id' designates that this is an update action
        // the type should only be specified on create
        if (array_key_exists('offer_type_id', $this->data['Offer'])) {
            if ($this->data['Offer'] == null) {
                unset($this->data['Offer']['offer_type_id']);
            }
        }

        return parent::beforeValidate($options);
    }

    public function is_owned_by($offer_id, $user_id) {
        $company_id = $this->Company->field('id', array('user_id' => $user_id));
        $offer_company_id = $this->field('company_id', array('id' => $offer_id));
        return $company_id === $offer_company_id && $company_id !== false;
    }

}
