<?php

class Distance extends AppModel {

    public $name = 'Distance';
    public $belongsTo = array('User', 'Company');

    // Remove all records with passed user id
    public function remove($user_id) {
        $this->deleteAll(array('Distance.user_id' => $user_id), false);
    }
}
