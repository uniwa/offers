<?php

class Day extends AppModel {
    public $name = "Day";
    public $hasMany = 'WorkHour';
}
