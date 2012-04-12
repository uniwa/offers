<?php
if ($this->Session->read('Auth.User.id') == $offer['Company']['user_id'] &&
    $offer['Offer']['offer_state_id'] == OfferStates::Draft)
{
    echo $this->Html->link('Διαγραφή',
                           array(
                                'controller' => 'offers',
                                'action' => 'delete',
                                $offer['Offer']['id']),
                            array(),
                           'Να διαγραφεί η προσφορα;').'<br/>';

    echo $this->Html->link('Επεξεργασία', array(
                                            'controller' => 'offers',
                                            'action' => 'edit',
                                            $offer['Offer']['id'])).'<br/>';

}


echo 'Προσφορά '.$offer['Offer']['id'].'<br/>';
switch($offer['Offer']['offer_type_id']){
    case 1:
        $label_class = 'label-info';
        break;
    case 2:
        $label_class = 'label-warning';
        break;
    case 3:
        $label_class = 'label-success';
        break;
}
$label_text = offer_type($offer['Offer']['offer_type_id']);
echo "<p><span class='label {$label_class}'>{$label_text}</span></p>";
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

if ($this->Session->read('Auth.User.role') === 'student' &&
    $offer['OfferCategory']['name'] !== 'happy hour')
{
    echo '<br/><br/>';
    echo $this->Form->create(false, array('type' => 'post',
                                          'url' => array('controller' => 'coupons',
                                                         'action' => 'add')
                                         ));
    echo $this->Form->hidden('Coupon.offer_id', array('value' => $offer['Offer']['id']));
    echo $this->Form->hidden('Coupon.student_id', array('value' => $student['Student']['id']));
    echo $this->Form->end('Get Coupon');
}

if (!empty($offer['Image']))
    foreach ($offer['Image'] as $image)
        echo $this->Html->image('/images/view/'.$image['id']);
