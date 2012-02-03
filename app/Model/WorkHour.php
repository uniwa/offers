<?php

class WorkHour extends AppModel{
    public $name = 'WorkHour';
    public $belongsTo = array('Company', 'Day',
                              'Offer' => array(
                                    'counterCache' => true
                               )
                        );

}
