<?php

echo 'Προσφορά '.$offer['Offer']['id'].'<br/>';

if (isset($offer['Company']['name']))
    echo 'Τίτλος: '.$offer['Offer']['title'].'<br/>';

if (isset($offer['Offer']['description']))
    echo 'Περιγραφή: '.$offer['Offer']['description'].'<br/>';

if (isset($offer['OfferCategory']['name']))
    echo 'Κατηγορία: '.$offer['OfferCategory']['name'].'<br/>';

if (isset($offer['OfferType']['name']))
    echo 'Είδος: '.$offer['OfferType']['name'].'<br/>';

if (isset($offer['Offer']['starting']))
    echo 'Έναρξη προσφοράς: '.$offer['Offer']['starting'].'<br/>';

if (isset($offer['Offer']['ending']))
    echo 'Λήξη προσφοράς: '.$offer['Offer']['ending'].'<br/>';

if (isset($offer['Offer']['expiration_date']))
    echo 'Ισχύει έως: '.$offer['Offer']['expiration_date'].'<br/>';

if (isset($offer['Offer']['coupon_count']) && $offer['Offer']['coupon_count'] >= 0) {
    echo 'Δεσμευμένα κουπόνια: '.$offer['Offer']['coupon_count'].
         (isset($offer['Offer']['total_quantity'])
            ? ' από '.$offer['Offer']['total_quantity']
            : '') .
         '<br/>';
}

if (isset($offer['Image']['id']))
    echo $this->Html->image('/images/view/'.$offer['Image']['id']);
