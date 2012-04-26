<?php
// TODO: move to controller
$is_user_the_owner = $this->Session->read('Auth.User.id') == $offer['Company']['user_id'];
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

if ($this->Session->read('Auth.User.id') != $offer['Company']['user_id'] ) {
    echo $this->Html->link('Εταιρία: ' . $offer['Company']['name'],
        array(
            'controller' => 'companies',
            'action' => 'view',
            $offer['Company']['id']
        )
    );
}
if ($is_spam) {
    echo 'Η προσφορά έχει χαρακτηρισθεί ως SPAM.<br/><br/>';
}

if ($is_user_the_owner) {
    if ($offer_state_id == STATE_ACTIVE) {
      echo $this->Html->link(
          '[Τερματισμός]',
          array(
              'controller' => 'offers',
              'action' => 'terminate_from_offer',
              $offer['Offer']['id']),
          null,
          'Ο τερματισμός μίας προσφοράς δεν μπορεί να αναιρεθεί. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');

    } else if ($offer_state_id == STATE_DRAFT) {

      echo $this->Html->link(
          '[Ενεργοποίηση]',
          array(
              'controller' => 'offers',
              'action' => 'activate_from_offer',
              $offer['Offer']['id']),
          null,
          'Οι ενεργοποιημένες προσφορές δε δύναται να τροποποιηθούν. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
    }
}

echo '<br>';

$html = '';
foreach($offer_info as $elem) {
    $html .= "<strong>{$elem['label']}:</strong> {$elem['value']}<br />";
}
echo $html;

if ($this->Session->read('Auth.User.role') === ROLE_STUDENT &&
    $offer['Offer']['offer_type_id'] !== TYPE_HAPPYHOUR) {
    echo '<br/><br/>';
    if ($offer['Offer']['offer_type_id'] == TYPE_COUPONS) {
        if ($offer['Offer']['coupon_count'] < $offer['Offer']['total_quantity']) {
            $get_coupon = $this->Form->create(false, array('type' => 'post',
                'url' => array('controller' => 'coupons', 'action' => 'add')));
            $get_coupon .= $this->Form->hidden('Coupon.offer_id',
                array('value' => $offer['Offer']['id']));
            $get_coupon .= $this->Form->end('Get Coupon');
            echo $get_coupon;
        }
    }
}

if (!empty($offer['Image']))
    foreach ($offer['Image'] as $image)
        echo $this->Html->image('/images/view/'.$image['id']);
