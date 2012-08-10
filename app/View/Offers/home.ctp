<?php
$html = '';
$html .= "<div class='span9'>";

$html .= "<div id='home-text' class='well'>";
$html .= "<span class='label label-info'>Happy Hour</span>";
$html .= "<p>Οι προσφορές τύπου Happy Hour επαναλαμβάνονται κάθε εβδομάδα σε συγκεκριμένες μέρες και ώρες</p>";
$html .= "<span class='label label-warning'>Coupons</span>";
$html .= "<p>Οι προσφορές τύπου Coupons προσφέρουν περιορισμένο αριθμό κουπονιών, τα οποία μπορούν να δεσμέυσουν οι φοιτητές.";
$html .= " Στη συνέχεια μπορούν να τα εξαργυρώσουν στην επιχείρηση, λαμβάνοντας το προϊόν ή την υπηρεσία που προσφέρει.</p>";
$html .= "<span class='label label-success'>Limited</span>";
$html .= "<p>Οι προσφορές τύπου Limited έχουν περιορισμένη διάρκεια.</p>";
$html .= "</div>";
$html .= "<p><h4>Πρόσφατες προσφορές:</h4></p>";
if (empty($offers)) {
    $html .= $this->element('alert', array(
        'type' => 'info',
        'label' => '<span class="label label-info">Offers</span>',
        'message' => 'Δεν υπάρχουν προσφορές'));
} else {
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

echo $html;
