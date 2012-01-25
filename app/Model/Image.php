<?php

class Image extends AppModel {

    public $name = 'Image';
    public $hasOne = array('Student', 'Company');
    public $belongsTo = array('ImageCategory',
                              'Offer' => array(
                                    'counterCache' => true
                              )
                        );
}

