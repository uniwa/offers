<?php
$html = '';

$html .= "<div class='span8'>";
$html .= "<div id=home-cont>";
$html .= "<div id='home-text' class='well'>";
$html .= "<h3>Έξυπνες Φοιτητικές Προσφορές</h3>";
$html .= "<p></p><p>Καλώς ήλθατε στην ιστοσελίδα των Έξυπνων Φοιτητικών Προσφορών που δημιουργήθηκε στο πλαίσιο της υπηρεσίας
         Δικτύωσης της Φοιτητικής Κοινότητας με τις τοπικές επιχειρήσεις. Η υπηρεσία απευθύνεται σε επιχειρήσεις των
         Καλλικρατικών Δήμων Αττικής που επιθυμούν να προσφέρουν εκπτώσεις και προσφορές σε φοιτητές του ΤΕΙ Αθήνας καθώς και
         στους φοιτητές μας που επιθυμούν να ενημερωθούν και να αξιοποιήσουν αυτές τις προσφορές.</p>";
$html .= "<p>Η υπηρεσία δίνει τη δυνατότητα στις επιχειρήσεις να αναρτούν προσφορές και εκπτώσεις για τους φοιτητές μας χωρίς 
        κανένα οικονομικό και διαχειριστικό κόστος.</p>";
$offer_link_html = $this->Html->link("Happy Hour, Coupons, Limited",
        array('controller' => 'offers', 'action' => 'offertypes'));
$html .= "<p>Τα είδη των φοιτητικών προσφορών είναι {$offer_link_html}.</p>";
$html .= "<p>Η πρόσβαση στην εφαρμογή μπορεί να γίνεται και μέσω εφαρμογής κινητού τηλεφώνου (iPhone)</p>";
$html .= "</div>";

// do not show login form if user is logged in
if (is_null($role)) {
    $html .= "<div id='login-form'>";
    $html .= $this->Form->create('User', array('action' => 'login', 'id' => ''));
    $html .= "<div><p>Αν είστε φοιτητής εισάγετε τα στοιχεία πρόσβασης των δικτυακών υπηρεσιών του ΤΕΙ Αθήνας (e-mail, wifi, εύδοξος):</p></div>";
    $html .= "<div><div>";
    $html .= $this->Form->input('username', array('label' => 'Όνομα χρήστη',
        'type' => 'text', 'autofocus' => 'autofocus'));
    $html .= "</div><div>";
    $html .= $this->Form->input('password', array('label' => 'Συνθηματικό'));
    $html .= "</div><div>";
    $html .= $this->Form->submit('Είσοδος', array('name' => 'login',
        'class' => 'button'));
    $html .= "</div></div><div><p></p><p>Αν είστε επιχείρηση θα πρέπει να έχετε ήδη εγγραφεί στην υπηρεσία μέσω της επιλογής ";
    $html .= $this->Html->link('εγγραφή επιχείρησης',
        array('controller' => 'users', 'action' => 'register'));
    $html .= "</p></div>";
    $html .= $this->Form->end();
    $html .= "</div>";
}

$html .= "</div><div class='clear-both'></div>";

$html .= "<h3>Πρόσφατες προσφορές&nbsp;<i title='Οι τελευταίες 5 αναρτημένες προσφορές' 
    class='icon-info-sign'></i></h3><br />";

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
    $html .= '<div class="well bold">Πατήστε '
        . $this->Html->link('εδώ', array(
            'controller' => 'offers', 'action' => 'index')
        )
        .' για όλες τις προσφορές.</div>';
}
$html .= "</div>";

// Twitter script
$html .= $this->Html->script('twitter');

echo $html;
