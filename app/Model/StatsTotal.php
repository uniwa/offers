<?php

class StatsTotal extends AppModel {

    public $name = 'StatsTotal';
    public $useTable = 'stats_total';
    public $belongsTo = array('Offer', 'Company');

    // Create and add a new record containing statistics for a single day
    // $data should be an array containing the following key/value pairs:
    // offer_id: offer id
    // company_id: company id
    // visit_date: date of stats
    // visits_total: total visits on given date
    // visits_unique: unique visitors on given date
    public function add_daily_stats($data) {
        $params = array(
            'recursive' => -1,
            'conditions' => array(
                'StatsTotal.visit_date' => $data['visit_date'],
                'StatsTotal.offer_id' => $data['offer_id']));
        $offer_found = $this->find('first', $params);
        if (is_null($offer_found)) {
            // no record found, create new
            $this->create();
        } else {
            // record already exists for given date and offer, update
            $data['id'] = $offer_found['StatsTotal']['id'];
        }

        return $this->save($data, false);
    }

    // Returns total visits and unique visitors (same IP address)
    // for a given offer
    public function get_visits($offer_id) {
        $params = array(
            'recursive' => -1,
            'conditions' => array('offer_id' => $offer_id));

        $params['fields'] = 'SUM(visits_total) as vsum';
        $vtotal = $this->find('first', $params);
        $visits['total'] = $vtotal[0]['vsum'];

        $params['fields'] = 'SUM(visits_unique) as vsum';
        $vunique = $this->find('first', $params);
        $visits['unique'] = $vunique[0]['vsum'];

        return $visits;
    }

}
