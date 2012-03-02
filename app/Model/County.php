<?php

class County extends AppModel {

    public $name = 'County';
    public $hasMany = array('Municipality');
}
