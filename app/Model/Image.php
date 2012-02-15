<?php

class Image extends AppModel {

    public $name = 'Image';
    public $hasOne = array('Student');
    public $belongsTo = array('ImageCategory',
                              'Company' => array(
                                    'counterCache' => true
                              ),
                              'Offer' => array(
                                    'counterCache' => true
                              )
                        );
}

