<?php

if ($this->Session->read('Auth.User.id') == $offer['Company']['user_id'] &&
    $offer['Offer']['offer_state_id'] == OfferStates::Draft)
{
    echo $this->Html->link('Διαγραφή', array(
                                        'controller' => 'offers',
                                        'action' => 'delete',
                                        $offer['Offer']['id'])).'<br/>';

    echo $this->Html->link('Επεξεργασία', array(
                                            'controller' => 'offers',
                                            'action' => 'edit',
                                            $offer['Offer']['id'])).'<br/>';

}


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

if (!empty($offer['Image']))
    foreach ($offer['Image'] as $image)
        echo $this->Html->image('/images/view/'.$image['id']);
