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

    // Returns visits and unique visitors (same IP address)
    // for all offers for a given date
    public function get_all_visits($date) {
        $date_base = "{$date['y']}-{$date['m']}-{$date['d']} ";
        $date_begin = $date_base."00:00:00";
        $date_end = $date_base."23:59:59";

        $params = array(
            'recursive' => -1,
            'conditions' => array(
                'created >' => $date_begin,
                'created <' => $date_end));

        $params['fields'] = array('StatsToday.offer_id', 'StatsToday.company_id');
        $offers = $this->find('list', $params);

        $visits = array();
        foreach($offers as $offer_id => $company_id) {
            $visits[$offer_id] = $this->get_visits($offer_id, $date);
            $visits[$offer_id]['cid'] = $company_id;
        }

        return $visits;
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

    // Deletes old visits stats
    // removes all records up until 2 days ago
    // leaves yesterday's and today's records
    public function delete_old_visits() {
        $day_before = date('Y-m-d', strtotime('-1 day'));

        $conditions = array('StatsToday.created <' => $day_before);
        $deleted = $this->deleteAll($conditions, false);

        return $deleted;
    }

}
