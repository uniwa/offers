<?php

class Company extends AppModel {

    public $name = 'Company';
    public $belongsTo = 'User';
    public $hasMany = 'Offer';
}
