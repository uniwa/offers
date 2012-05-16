<?php

class Vote extends AppModel {

    public $name = 'Vote';
    public $belongsTo = array('Student',
                              'Offer' => array(
                                    'counterCache' => true
                                ));

    public function has_voted($offer_id, $student_id) {
        $conditions = array('Vote.offer_id' => $offer_id);
        $vote_student = $this->field('student_id', $conditions);
        return $vote_student === $student_id;
    }

}
