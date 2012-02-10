<?php

class WorkHour extends AppModel{
    public $name = 'WorkHour';
    public $belongsTo = array('Company', 'Day',
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
