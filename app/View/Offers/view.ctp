<?php
// TODO: move to controller
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

$is_spam = $offer['Offer']['is_spam'];
$offer_state_id = $offer['Offer']['offer_state_id'];

// TODO: move to controller
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
echo "<h4>Προσφορά {$offer['Offer']['id']}</h4><br/>";
if ($is_spam) {
    echo 'Η προσφορά έχει χαρακτηρισθεί ως SPAM.<br/><br/>';
}

if ($offer_state_id == STATE_ACTIVE) {

    if ($this->Session->read('Auth.User.id') == $offer['Company']['user_id'] ) {
      echo $this->Html->link(
          '[Τερματισμός]',
          array(
              'controller' => 'offers',
              'action' => 'terminate_from_offer',
              $offer['Offer']['id']),
          null,
          'Ο τερματισμός μίας προσφοράς δεν μπορεί να αναιρεθεί. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
    }
    echo '<br>';
}

$html = '';
foreach($offer_info as $elem) {
    $html .= "<strong>{$elem['label']}:</strong> {$elem['value']}<br />";
}
echo $html;

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
