<?php

class Student extends AppModel {

    public $name = 'Student';
    public $belongsTo = 'User';
    public $hasMany = 'Coupons';
}
