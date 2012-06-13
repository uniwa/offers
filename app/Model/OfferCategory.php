<?php

class OfferCategory extends AppModel {

    public $name = 'OfferCategory';
    public $hasMany = 'Offer';
    public $validate = array(
        'name' => array(
            'not_empty' => array(
                'rule' => 'alphaNumeric',
                'notEmpty' => true,
                'required' => true,
                'message' => 'Επιτρέπονται γράμματα και αριθμοί'
            ),
        )
    );
}
