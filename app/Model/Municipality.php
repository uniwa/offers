<?php

class Municipality extends AppModel {

    public $name = 'Municipality';
    public $belongsTo = array('County');
}
