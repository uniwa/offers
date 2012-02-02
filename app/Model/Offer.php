<?php

class Offer extends AppModel {

    public $name = 'Offer';
    public $belongsTo = array('Company', 'OfferType', 'OfferCategory');
    public $hasMany = array('Coupon', 'Image', 'WorkingHour');
}
