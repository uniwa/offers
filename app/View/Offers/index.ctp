<?php
$sidebar_params = array('offer_categories' => $offer_categories,
                        'municipalities' => $municipalities,
                        'type_stats' => $type_stats);

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
$offers_count = $this->Paginator->counter(array('format' => '%count%'));
$html .= "<p>Βρέθηκαν <span class='bold'>{$offers_count}</span> προσφορές";

// determine what text to append to the above statement describing the filters
// currently applied to the search
if (isset($filter)) {
    $value = $filter['value'];
    switch ($filter['for']) {
        case 'none':
            $html .= ' συνολικά';
            break;
        case 'type':
            switch ($value) {
                case TYPE_HAPPYHOUR: $class = 'label-info';    break;
                case TYPE_COUPONS  : $class = 'label-warning'; break;
                case TYPE_LIMITED  : $class = 'label-success';
            }
            $type = offer_type($value);
            $html .= " <span class='label $class'>$type</span>";
            break;
        case 'cat':
            $html .= " στην κατηγορία &laquo;$value&raquo;";
            break;
        case 'tag':
            $html .= " που έχουν τη λέξη-κλειδί &laquo;$value&raquo;";
            break;
        case 'search':
            if (isset($filter['value']['municipality']))
                $html .= " στο δήμο {$value['municipality']}";

            if (isset($filter['value']['alphanum']))
                $html .= " που περιέχουν: {$value['alphanum']}";
            break;
        case 'spam':
            $html .= ' που έχουν σημανθεί ως' .
                     ' <span class="label label-important">αναρμοστες</span>';
    }
}
$html .= "</p>";

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
        $title = $offer['Offer']['title'];
        $label = "<span class='label label-{$tag_class}'>{$tag_name}</span>";
        $vote_count = $offer['Offer']['vote_count'];
        $vote_plus = $offer['Offer']['vote_plus'];
        $vote_minus = $offer['Offer']['vote_minus'];
        $votes_html = "<span class='votes green'>+{$vote_plus}</span> ";
        $votes_html .= "<span class='votes red'>-{$vote_minus}</span> ";
        $postfix = ($vote_count == 1)?'ς':'ι';
        $offer_class = '';
        // only show number of votes to owner
        if (($this->Session->read('Auth.User.role') === ROLE_COMPANY)
            && ($this->Session->read('Auth.Company.id') === $offer['Offer']['company_id'])) {
            $votes_html .= "({$vote_count} ψήφο{$postfix})";
            $offer_class = 'offer-owner';
        }

        // offer tile container
        $html .= "<div class='offer-tile {$offer_class}'>";
        // offer thumb image or default logo
        $image_url = "/img/logothumb.png";
        if (!empty($offer['Image'])) {
            $image_url = "/images/thumb/".$offer['Image'][0]['id'];
        }
        $html .= "<div class='offer-thumb'>";
        $image_thumb = $this->Html->image($image_url,
            array('alt' => $offer['Offer']['title']));
        $html .=  $this->Html->link($image_thumb,
            array('controller' => 'offers', 'action' => 'view', $offer['Offer']['id']),
            array('escape' => false));
        $html .= "</div>";

        // offer information
        $html .= "<div class='offer-info-cont'>";
        $html .= "<div class='offer-info'>";
        $html .= "<div class='offer-header'>";

        // offer title
        $html .= "<div class='offer-name-cont'>";
        $html .= "<div class='offer-name'>";
        $html .=  $this->Html->link($title,
            array('controller' => 'offers', 'action' => 'view', $offer['Offer']['id']));
        $html .= "</div>";
        $html .= "</div>";

        // offer type label and votes
        $html .= "<div class='offer-label'>{$label}</div>";
        $html .= "<div class='offer-votes'>{$votes_html}</div>";

        // Twitter settings
        // TODO: create route 'http://coupons.teiath.gr/5' -> '[...]/offers/view/5'
        //       and use it as url to tweet
        $url = APP_URL."/offers/view/{$offer['Offer']['id']}";
        $text = "{$offer['Offer']['title']},";
        $count = "none";
        $related = TWITTER_SCREEN_NAME.":".TWITTER_FULL_NAME;

        $html .= "<div class='offer-tweet'>";
        $html .= "<a href='https://twitter.com/share' data-count='{$count}' ";
        $html .= "class='twitter-share-button' data-lang='el' ";
        $html .= "data-related='{$related}' data-text='{$text}' ";
        $html .= "data-url='{$url}'>Tweet</a>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";

        // if offer improper, display special tag (for admin)
        if ($show_flag_link) {
            $flag_icon = $this->Html->tag('i', '', array('class' => 'icon-flag'));

            $html .= "<div class='offer-improper'>";
            $html .= $this->Html->link(
                    $flag_icon . ' Ανάρμοστη',
                    array('controller' => 'offers',
                          'action' => 'improper',
                           $offer['Offer']['id']),
                    array('escape' => false,
                          'class' => 'btn btn-mini')
            );
            $html .= "</div>";
        }

        // print tags as links if available
        if ($offer['Offer']['tags'] == NULL){
            $html .= "<div class='offer-tags'>&nbsp;</div>";
        } else {
            // where tag links should go
            $tag_link = array('controller' => 'offers', 'action' => 'tag');
            // use helper to generate tags
            $tag_options = array('element' => 'div', 'link' => $tag_link, 'class' => 'offer-tags');
            $html .= $this->Tag->generate($offer['Offer']['tags'], $tag_options);
        }

        $html .= "</div>";
        $html .= "</div>";
    }
}

// Twitter script
$html .= $this->Html->script('twitter');

$this->Paginator->options(array('url' => $this->passedArgs));
$html .= "<div class = 'pagination'><ul>";
$html .= $this->Paginator->numbers(array(
    'first' => 2,
    'last' => 2,
    'modulus' => 3,
    'separator' => ' ',
    'ellipsis' => "<li><a>...</a></li>",
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
