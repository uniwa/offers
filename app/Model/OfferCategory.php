<?php

class OfferCategory extends AppModel {

    public $name = 'OfferCategory';
    public $hasMany = 'Offer';
}
