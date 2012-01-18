<?php

class Image extends AppModel {

    public $name = 'Image';
    public $hasOne = array('Student', 'Company', 'Offer');
}

