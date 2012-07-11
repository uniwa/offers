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

    private $offer_joins = array(
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

    protected function _findCountOffers($state, $query, $results = array()) {
        if ($state === 'before') {

            $query['fields'] = array('OfferCategory.id',
                                     'OfferCategory.name',
                                     'COUNT(*) as offer_count');
            $query['joins'] = $this->offer_joins;
            $query['conditions'] = $this->Offer->conditionsValid;
            $query['group'] = 'OfferCategory.id';

            return $query;
        }

        $res = array();
        foreach ($results as &$record) {
            $record['OfferCategory']['offer_count'] = $record[0]['offer_count'];
            unset($record[0]);

            $res[] = $record['OfferCategory'];
        }
        return $res;
    }
}
