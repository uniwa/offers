<?php
App::uses('AppHelper', 'View/Helper');

class CouponsLayoutHelper extends AppHelper {
    private $css_label = array(
        TYPE_HAPPYHOUR => 'label-info',
        TYPE_COUPONS => 'label-warning',
        TYPE_LIMITED => 'label-success'
    );

    public function offer_label($offer_type_id) {
        /* return HTML Markup with the offer type text
         * in appropriate coloring
         */
        $text = offer_type($offer_type_id);
        $label = $this->css_label[$offer_type_id];
        $markup = "<span class='label {$label}'>{$text}</span>";
        return $markup;
    }

}
