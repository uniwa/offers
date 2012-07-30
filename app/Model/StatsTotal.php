<?php

class StatsTotal extends AppModel {

    public $name = 'StatsTotal';
    public $useTable = 'stats_total';
    public $belongsTo = array('Offer', 'Company');

    // Create and add a new record containing statistics for a single day
    // $data should be an array containing the following key/value pairs:
    // oid: offer_id
    // cid: company_id
    // date: date of stats
    // total: total visits on given date
    // unique: unique visitors on given date
    public function add_daily_stats($data) {
        return $this->save($data, false);
    }

    // Returns total visits and unique visitors (same IP address)
    // for a given offer
    public function get_visits($offer_id) {
        $params = array(
            'recursive' => -1,
            'conditions' => array('offer_id' => $offer_id));

        $params['fields'] = "visits_total";
        $visits['total'] = $this->find('count', $params);

        $params['fields'] = "visits_unique";
        $visits['unique'] = $this->find('count', $params);

        return $visits;
    }

}
