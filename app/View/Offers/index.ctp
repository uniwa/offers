<?php
$sidebar_params = array('count_by_category' => $count_by_category);
$show_flag_link = false;

// in this context, if `shows_spam' has been set (regardless of its value), then
// only spam offers are currently being rendered and logged-in user is admin.
// In case it has not been set, it is not guaranteed that the logged-in user in
// NOT an admin
if ($this->Session->read('Auth.User.role') == ROLE_ADMIN) {

    // whether to display flag-as-spam link after each offer
    $shows_spam = isset($shows_spam);
    $show_flag_link = ! $shows_spam;
    $sidebar_params['shows_spam'] = $shows_spam;
}

$html = '';
$html .= $this->element('sidebar', $sidebar_params);
$html .= "<div class='span9'>";

if (empty($offers)) {
    $html .= $this->element('alert', array(
        'type' => 'info',
        'label' => '<span class="label label-info">Offers</span>',
        'message' => 'Δεν υπάρχουν προσφορές'));
} else {
    // Ordering
    $select_order = '';
    $orderby = (isset($this->params['named']['orderby']))
        ?$this->params['named']['orderby']:null;
    $action = $this->params['action'];
    $pass = (isset($this->params['pass'][0]))?$this->params['pass'][0]:null;
    $default_orderby = (($action === 'limited') && (is_null($pass)))?
        'autoend':'recent';
    $new_order = "<strong>{$order_options[$default_orderby]['title']}</strong>";
    foreach ($order_options as $k => $v) {
        $geolocation = $this->Session->read('Auth.User.geolocation');
        if ($k == 'distance')
            if (is_null($geolocation))
                continue;
        if ($k == 'autoend')
            if ($action !== 'limited')
                continue;
        if (!is_null($orderby) && ($k === $orderby)) {
            $select_order .= " <strong>{$v['title']}</strong>";
            continue;
        }
        if (is_null($orderby) && ($k === $default_orderby)) {
            $select_order .= " {$new_order}";
            continue;
        }
        $select_order .= " ".$this->Html->link($v['title'],
            array('action' => $action, $pass, 'orderby' => $k));
    }
    $html .= "<p>Ταξινόμηση: {$select_order}</p><br />";

    //offers
    // TODO: make this a f***** list - stop the <br/> abuse
    // (when dealing with layout)
    foreach ($offers as $key => $offer) {
        $offer_type_id = $offer['Offer']['offer_type_id'];
        $tag_classes = array('info', 'warning', 'success');
        $tag_class = $tag_classes[$offer_type_id - 1];
        $tag_name = offer_type($offer_type_id);
        $title = $offers[$key]['Offer']['title'];
        $label = "<span class='label label-{$tag_class}'>{$tag_name}</span>";
        $vote_count = $offers[$key]['Offer']['vote_count'];
        $vote_plus = $offers[$key]['Offer']['vote_plus'];
        $vote_minus = $offers[$key]['Offer']['vote_minus'];
        $votes_html = "<span class='votes green'>+{$vote_plus}</span> ";
        $votes_html .= "<span class='votes red'>-{$vote_minus}</span> ";
        $postfix = ($vote_count == 1)?'ς':'ι';
        $votes_html .= "({$vote_count} ψήφο{$postfix})";
        $html .= "<p>";
        $html .=  $this->Html->link($title,
            array('action' => 'view', $offers[$key]['Offer']['id']));
        $html .= " {$label} {$votes_html}";

        if ($show_flag_link) {
            $flag_icon = $this->Html->tag('i', '', array('class' => 'icon-flag'));

            $html .= $this->Html->link(
                    $flag_icon . ' Σήμανση ως SPAM',
                    array('controller' => 'offers',
                          'action' => 'flag',
                           $offer['Offer']['id']),
                    array('escape' => false,
                          'class' => 'btn btn-mini'),
                    'Η ενέργεια δεν δύναται να αναιρεθεί. Είστε βέβαιοι;'
            );
        }

        $html .= "<br /><i>{$offer['Offer']['modified']}</i>";

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

        $html .= "<br />";
        $html .= "<a href='https://twitter.com/share' data-count='{$count}' ";
        $html .= "class='twitter-share-button' data-lang='el' ";
        $html .= "data-related='{$related}' data-text='{$text}' data-url='{$url}'>Tweet</a>";
        $html .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];";
        $html .= "if(!d.getElementById(id)){js=d.createElement(s);js.id=id;";
        $html .= "js.src='//platform.twitter.com/widgets.js';";
        $html .= "fjs.parentNode.insertBefore(js,fjs);}}";
        $html .= "(document,'script','twitter-wjs');</script>";

        // print tags as links if available
        if ($offer['Offer']['tags'] == NULL){
            $html .= "</p><br />";
            continue;
        }

        // where tag links should go
        $tag_link = array('controller' => 'offers', 'action' => 'tag');
        // use helper to generate tags
        $tag_options = array('element' => 'p', 'link' => $tag_link);
        $html .= $this->Tag->generate($offer['Offer']['tags'], $tag_options);
    }
}
$this->Paginator->options(array('url' => $this->passedArgs));
$html .= "<div class = 'pagination'><ul>";
$html .= $this->Paginator->numbers(array(
    'first' => 2,
    'last' => 2,
    'modulus' => 3,
    'separator' => ' ',
    'tag' => 'li'));
$html .= "</ul></div></div>";

// Request geolocation iformation for logged user if not already set
$html .= "<script>var baseUrl = '".APP_URL."';</script>";
if ($this->Session->check('Auth.User')) {
    if ($this->Session->read('Auth.User.role') === ROLE_STUDENT) {
        if (is_null($this->Session->read('Auth.User.geolocation'))) {
            $html .= $this->Html->script('geolocation');
        }
    }
}

echo $html;
