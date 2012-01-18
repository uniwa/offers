<?php

class Student extends AppModel {

    public $name = 'Student';
    public $belongsTo = array('User', 'Image');
    public $hasMany = 'Coupons';
}
