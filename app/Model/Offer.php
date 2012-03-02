<?php

class Offer extends AppModel {

    public $name = 'Offer';
    public $belongsTo = array('Company', 'OfferType', 'OfferCategory', 'OfferState');
    public $hasMany = array('Coupon', 'Image', 'WorkHour');


}
