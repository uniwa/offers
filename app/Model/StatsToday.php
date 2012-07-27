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

}
