<?php

class OfferCategory extends AppModel {

    public $name = 'OfferCategory';
    public $hasMany = 'Offer';
    public $findMethods = array(
        'countOffers' => true
    );
    public $validate = array(
        'name' => array(
            'not_empty' => array(
                'rule' => 'notEmpty',
                'required' => true,
                'message' => 'Συμπληρώστε την ονομασία της κατηγορίας.',
                'last' => true,
            ),
            'valid' => array(
                'rule' => '/^[\w\dαβγδεζηθικλμνξοπρστυφχψωΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩΆάΈέΎΉήύΊίΌόΏώϊϋΐΰς\-,. &]+$/',
                'message' => 'Επιτρέπονται γράμματα, αριθμοί και τα σύμβολα -,.&',
            ),
        )
    );


    protected function _findCountOffers($state, $query, $results = array()) {
        if ($state === 'before') {
            $joins = array(
                        array('table' => 'offers',
                              'alias' => 'Offer',
                              'type' => 'LEFT',
                              'conditions' => array(
                                  'OfferCategory.id = Offer.offer_category_id',
                               )),
                        array('table' => 'companies',
                              'alias' => 'Company',
                              'type' => 'LEFT',
                              'conditions' => array(
                                  'Company.id = Offer.company_id',
                               )));
            $query['fields'] = array('OfferCategory.id',
                                     'COUNT(*) as offer_count');
            $query['joins'] = $joins;
            $query['conditions'] = array('Offer.offer_state_id' => STATE_ACTIVE,
                                         'Offer.is_spam' => 0,
                                         'Company.is_enabled' => 1);
            $query['group'] = 'OfferCategory.id';

            return $query;
        }
        return Set::combine(
                $results, '{n}.OfferCategory.id', '{n}.{n}.offer_count');
    }
}
