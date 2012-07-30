<?php

class StatsToday extends AppModel {

    public $name = 'StatsToday';
    public $useTable = 'stats_today';
    public $belongsTo = array('Offer', 'Company');

    // Create and add a new record containing statistics for a single visit
    // $data should be an array containing the following key/value pairs:
    // oid: offer_id
    // cid: company_id
    // ip: IP address of visitor
    public function add_visit($data) {
        return $this->save($data, false);
    }

    // Returns total visits and unique visitors (same IP address)
    // for a given offer and date
    public function get_visits($offer_id, $date) {
        $date = "{$date['y']}-{$date['m']}-{$date['d']} ";
        $date_begin = $date."00:00:00";
        $date_end = $date."23:59:59";

        $params = array(
            'recursive' => -1,
            'conditions' => array(
                'offer_id' => $offer_id,
                'created >' => $date_begin,
                'created <' => $date_end));

        $visits['total'] = $this->find('count', $params);

        $params['fields'] = "DISTINCT ip";
        $visits['unique'] = $this->find('count', $params);

        return $visits;
    }

}
