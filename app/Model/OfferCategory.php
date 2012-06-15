<?php

class OfferCategory extends AppModel {

    public $name = 'OfferCategory';
    public $hasMany = 'Offer';
    public $validate = array(
        'name' => array(
            'not_empty' => array(
                'rule' => 'notEmpty',
                'required' => true,
                'message' => 'Συμπληρώστε την ονομασία της κατηγορίας.',
                'last' => true,
            ),
            'valid' => array(
                'rule' => '/^[\w\dαβγδεζηθικλμνξοπρστυφχψωΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩΆάΈέΎΉήύΊίΌόΏώϊϋΐΰς\-,. &]+$/',
                'message' => 'Επιτρέπονται γράμματα, αριθμοί και τα σύμβολα -,.&',
            ),
        )
    );
}
