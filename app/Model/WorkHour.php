<?php

class WorkHour extends AppModel{
    public $name = 'WorkHour';
    public $belongsTo = array('Day',
                              'Company' => array(
                                    'counterCache' => true
                              ),
                              'Offer' => array(
                                    'counterCache' => true
                               )
                           );

    public $validate = array(

        'day_id' => array(
                'rule' => '/^[1-7]{1}$/'
        )
    );

}
