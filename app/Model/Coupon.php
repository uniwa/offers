<?php

class Coupon extends AppModel {

    public $name = 'Coupon';
    public $belongsTo = array('Students', 'Offer');
}
