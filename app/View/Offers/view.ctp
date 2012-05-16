<?php
$html = '';

// TODO: move to controller
$is_user_the_owner = $this->Session->read('Auth.User.id') == $offer['Company']['user_id'];
if ($this->Session->read('Auth.User.id') == $offer['Company']['user_id'] &&
    $offer['Offer']['offer_state_id'] == OfferStates::Draft)
{
    $html .= $this->Html->link('Διαγραφή', array(
        'controller' => 'offers', 'action' => 'delete', $offer['Offer']['id']),
        array(), 'Να διαγραφεί η προσφορα;');
    $html .= '<br>';

    $html .= $this->Html->link('Επεξεργασία', array(
                                            'controller' => 'offers',
                                            'action' => 'edit',
                                            $offer['Offer']['id']));
    $html .= '<br>';

    $html .= $this->Html->link('Εικόνες', array(
                                            'controller' => 'offers',
                                            'action' => 'imageedit',
                                            $offer['Offer']['id']));
    $html .= '<br>';
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
$offer_type_id = $offer['Offer']['offer_type_id'];
$label_text = offer_type($offer_type_id);

$icon_thumbs_up = "<i class='icon-thumbs-up'></i>";
$icon_thumbs_down = "<i class='icon-thumbs-down'></i>";
$link_up = $this->Html->link($icon_thumbs_up,
    array('controller' => 'offers', 'action' => 'vote_up', $offer_type_id),
    array('escape' => false));
$link_down = $this->Html->link($icon_thumbs_down,
    array('controller' => 'offers', 'action' => 'vote_down', $offer_type_id),
    array('escape' => false));
$html .= "<p><span class='label {$label_class}'>{$label_text}</span></p>";
$html .= "<h4>Προσφορά {$offer['Offer']['id']}</h4>";
$html .= "<p>{$link_up} {$link_down}</p>";
if ($this->Session->read('Auth.User.id') != $offer['Company']['user_id'] ) {
    $html .= $this->Html->link('Εταιρία: '.$offer['Company']['name'], array(
        'controller' => 'companies', 'action' => 'view', $offer['Company']['id']));
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

      $html .= $this->Html->link(
          '[Ενεργοποίηση]',
          array(
              'controller' => 'offers',
              'action' => 'activate_from_offer',
              $offer['Offer']['id']),
          null,
          'Οι ενεργοποιημένες προσφορές δε δύνανται να τροποποιηθούν. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
    }
}

$html .= '<br>';

foreach($offer_info as $elem) {
    $html .= "<strong>{$elem['label']}:</strong> {$elem['value']}<br />";
}

if ($this->Session->read('Auth.User.role') === ROLE_STUDENT &&
    $offer['Offer']['offer_type_id'] !== TYPE_HAPPYHOUR) {
    $html .= "<br/><br/>";
    if ($offer['Offer']['offer_type_id'] == TYPE_COUPONS) {
        if ($offer['Offer']['coupon_count'] < $offer['Offer']['total_quantity']) {
            $html .= $this->Form->create(false, array('type' => 'post',
                'url' => array('controller' => 'coupons',
                               'action' => 'add',
                               $offer['Offer']['id']
                         )));
            $html .= $this->Form->end('Get Coupon');
        }
    }
}

if (!empty($offer['Image']))
    foreach ($offer['Image'] as $image)
        if ($image['image_category_id'] == IMG_NORMAL) {
            $html .= "<div class='image_frame'>";
            $html .= $this->Html->image('/images/view/'.$image['id']);
            $html .= "</div>";
        }

echo $html;
