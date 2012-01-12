<?php
    
class User extends AppModel {
    public $name = 'User';
    public $hasOne = array( 'Company', 'Student' );

}

