<?php

class ImageCategory extends AppModel {

    public $name = 'ImageCategory';
    public $hasMany = array('Image');
}
