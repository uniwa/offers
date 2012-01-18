<?php

class Company extends AppModel {

    public $name = 'Company';
    public $belongsTo = array('User', 'Image');
    public $hasMany = 'Offer';
}
