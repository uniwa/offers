<?php
$html = '';

// TODO: move to controller
$offer_state_id = $offer['Offer']['offer_state_id'];
$offer_type_id = $offer['Offer']['offer_type_id'];
$label_text = offer_type($offer_type_id);
$is_spam = $offer['Offer']['is_spam'];
$is_user_the_owner = $this->Session->read('Auth.User.id') == $offer['Company']['user_id'];
$is_offer_draft = $offer_state_id == STATE_DRAFT;
$is_offer_active = $offer_state_id == STATE_ACTIVE;

// Offer actions (copy,images, etc.)
if ($is_user_the_owner) {
    $html .= $this->Html->link('Αντιγραφή', array(
        'controller' => 'offers',
        'action' => 'copy',
        $offer['Offer']['id']));
    $html .= '<br>';

    if ($is_offer_draft) {
        $html .= $this->Html->link('Διαγραφή', array(
            'controller' => 'offers',
            'action' => 'delete',
            $offer['Offer']['id']),
            array(), 'Να διαγραφεί η προσφορα;');
        $html .= '<br>';

        $html .= $this->Html->link('Επεξεργασία', array(
            'controller' => 'offers',
            'action' => 'edit',
            $offer['Offer']['id']));
        $html .= '<br>';

        $html .= $this->Html->link(
          '[Ενεργοποίηση]', array(
                'controller' => 'offers',
                'action' => 'activate',
                $offer['Offer']['id']), null,
                'Οι ενεργοποιημένες προσφορές δε δύνανται να τροποποιηθούν. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
        $html .= '<br>';
    }

    if ($is_offer_draft) {
        $html .= $this->Html->link('Εικόνες', array(
            'controller' => 'offers',
            'action' => 'imageedit',
            $offer['Offer']['id']));
        $html .= '<br>';

        $html .= $this->Html->link('[Τερματισμός]', array(
            'controller' => 'offers',
            'action' => 'terminate',
            $offer['Offer']['id']), null,
            'Ο τερματισμός μίας προσφοράς δεν μπορεί να αναιρεθεί. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
    }

}

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

$html .= "<p><span class='label {$label_class}'>{$label_text}</span></p>";

// administrator's flagging
if (isset($is_flaggable)) {

    $flag_icon = $this->Html->tag('i', '', array('class' => 'icon-flag'));

    $flag_link = $this->Html->link($flag_icon . ' Σήμανση ως SPAM',
                                   array('controller' => 'offers',
                                         'action' => 'flag',
                                          $offer['Offer']['id']),
                                   array('escape' => false,
                                         'class' => 'btn'),
                                   'Η ενέργεια δεν δύναται να αναιρεθεί. Είστε βέβαιοι;'
                                   );

    $html .= $flag_link;
}

$html .= "<h4>Προσφορά {$offer['Offer']['id']}</h4>";
if ($this->Session->read('Auth.User.id') != $offer['Company']['user_id'] ) {
    $html .= $this->Html->link('Εταιρία: '.$offer['Company']['name'], array(
        'controller' => 'companies', 'action' => 'view', $offer['Company']['id']));
}

if (!is_null($student_vote)) {
    $vote_class = ($student_vote)?'green':'red';
    $my_vote = ($student_vote)?'+1':'-1';
    $html .= "<div class='{$vote_class}'>{$my_vote}</div>";
}

if ($this->Session->read('Auth.User.role') === ROLE_STUDENT) {
    $icon_thumbs_up = "<i class='icon-thumbs-up'></i>";
    $icon_thumbs_down = "<i class='icon-thumbs-down'></i>";
    $icon_cancel = "<i class='icon-remove'></i>";
    $link_up = $this->Html->link($icon_thumbs_up,
        array('controller' => 'votes', 'action' => 'vote_up', $offer['Offer']['id']),
        array('escape' => false));
    $link_down = $this->Html->link($icon_thumbs_down,
        array('controller' => 'votes', 'action' => 'vote_down', $offer['Offer']['id']),
        array('escape' => false));
    $link_cancel = $this->Html->link($icon_cancel,
        array('controller' => 'votes', 'action' => 'vote_cancel', $offer['Offer']['id']),
        array('escape' => false));
    $html .= "<p>{$link_up} {$link_down} {$link_cancel}</p>";
}

// Twitter settings
// TODO: move to configuration?
// TODO: create route 'http://coupons.teiath.gr/5' -> '[...]/offers/view/5'
//       and use it as url to tweet
$screenname = "TEIATHCoupons";
$fullname = "TEIATH Coupons";
$baseurl = "http://coupons.edu.teiath.gr";
$url = "{$baseurl}/offers/view/{$offer['Offer']['id']}";
//$url = $baseurl.$this->Html->url(null);
$text = "Προσφορά: {$offer['Offer']['title']},";
$count = "none";
$related = $screenname.":".$fullname;

$html .= "<p><a href='https://twitter.com/share' data-count='{$count}' ";
$html .= "class='twitter-share-button' data-lang='el' ";
$html .= "data-related='{$related}' data-text='{$text}' data-url='{$url}'>Tweet</a>";
$html .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];";
$html .= "if(!d.getElementById(id)){js=d.createElement(s);js.id=id;";
$html .= "js.src='//platform.twitter.com/widgets.js';";
$html .= "fjs.parentNode.insertBefore(js,fjs);}}";
$html .= "(document,'script','twitter-wjs');</script></p>";

$tag_link = array('controller' => 'offers', 'action' => 'tag');
// use helper to generate tags
$tag_options = array('element' => 'span', 'link' => $tag_link, 'label' => '');
$offer_info['tags']['value'] = $this->Tag->generate($offer_info['tags']['value'], $tag_options);

foreach($offer_info as $elem) {
    $html .= "<strong>{$elem['label']}:</strong> {$elem['value']}<br />";
}



if ($this->Session->read('Auth.User.role') === ROLE_STUDENT &&
    $offer['Offer']['offer_type_id'] !== TYPE_HAPPYHOUR) {
    $html .= "<br/><br/>";
    if ($offer_type_id == TYPE_COUPONS) {
        // Check both coupon count and state, just in case
        if (($offer['Offer']['coupon_count'] < $offer['Offer']['total_quantity'])
            && ($offer['Offer']['offer_state_id'] == STATE_ACTIVE))
        {
            $html .= $this->Form->create(false, array('type' => 'post',
                'url' => array('controller' => 'coupons',
                               'action' => 'add',
                               $offer['Offer']['id']
                         )));
            $html .= $this->Form->end('Get Coupon');
        }
    }
}

if (!empty($offer['Image'])) {
    foreach ($offer['Image'] as $image) {
        $html .= "<div class='image_frame'>";
        $html .= $this->Html->image('/images/thumb/'.$image['id']);
        $html .= "</div>";
    }
}

echo $html;

// show coupons for offer
// only if visitor == owner and offer type = coupons
if (isset($is_owner) and $is_owner == true) {
?>
    <br />
    <div class="well">
        <h4>Κουπόνια</h4>
        <br />
        <table class="table table-condensed table-striped">
            <thead>
                <tr>
                    <th>Α/Α</th>
                    <th>Κωδικός κουπονιού</th>
                    <th>Ημ/νία δέσμευσης</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $counter = 0;
                    foreach ($coupons as $c) {
                        $counter++;

                        $date = $c['Coupon']['created'];
                        $serial_number = $c['Coupon']['serial_number'];

                        $coupon_link = $this->Html->link(
                            $serial_number,
                            array(
                                'controller' => 'coupons',
                                'action' => 'view',
                                $c['Coupon']['id']
                            ),
                            array()
                        );

                        echo "<tr>";
                        echo "<td>{$counter}</td>";
                        echo "<td>{$serial_number}</td>";
                        echo "<td>{$this->Time->format('d-m-Y',$date)}</td>";
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
<?php
}
