<?php
$happyhour = $this->Html->link(
    ucwords(offer_type(TYPE_HAPPYHOUR)),
    array('controller' => 'offers', 'action' => 'happyhour'),
    array('class' => '')
);

$happyhour_rss = $this->Html->link(
    ' ',
    array('controller' => 'offers', 'action' => 'happyhour.rss'),
    array('class' => 'rss-happyhour', 'title' => 'RSS feed for HappyHour offers')
);

$coupons = $this->Html->link(
    ucwords(offer_type(TYPE_COUPONS)),
    array('controller' => 'offers', 'action' => 'coupons'),
    array('class' => '')
);

$coupons_rss = $this->Html->link(
    '',
    array('controller' => 'offers', 'action' => 'coupons.rss'),
    array('class' => 'rss-coupons', 'title' => 'RSS feed for Coupon offers')
);

$limited = $this->Html->link(
    ucwords(offer_type(TYPE_LIMITED)),
    array('controller' => 'offers', 'action' => 'limited'),
    array('class' => '')
);

$limited_rss = $this->Html->link(
    '',
    array('controller' => 'offers', 'action' => 'limited.rss'),
    array('class' => 'rss-limited', 'title' => 'RSS feed for limited offers')
);

// get the number of offers per type
// any better ideas on how to implement this?
foreach ($type_stats as $stats) {
    switch ($stats['id']) {
        case TYPE_HAPPYHOUR: $happyhour_count = $stats['offer_count']; break;
        case TYPE_COUPONS: $coupons_count = $stats['offer_count']; break;
        case TYPE_LIMITED: $limited_count = $stats['offer_count']; break;
    }
}


if (isset($shows_spam)) {
    // if `shows_spam' has been set to true, then only spam offers are currently
    // being rendered, in which case a link to display non-spam offers should be
    // provided; if it has not been set at all then it is implied that the user
    // is not admin and no link should be provided at all
    $spam_link = $shows_spam
            ? $this->Html->link('Απόκρυψη ανάρμοστων',
                                array('controller' => 'offers',
                                      'action' => 'index'))
            : $this->Html->link('Εμφάνιση ανάρμοστων',
                                array('controller' => 'offers',
                                      'action' => 'spam'));
}

$html = '';
foreach($offer_categories as $cat) {
    $id = $cat['id'];
    $html .= "<li>";
    $html .= $this->Html->link($cat['name'], array(
        'controller' => 'offers', 'action' => 'category', $id));
    $html .= " ({$cat['offer_count']})";
    $html .= $this->Html->link(
        '',
        array('controller' => 'offers', 'action' => 'category', "{$id}.rss"),
        array('class' => 'rss-category', 'title' => "RSS feed for category {$id}")
    );

    $html .= "</li>";
}

if (!isset($search_string)) {
    $search_string = '';
}
if (!isset($municipality_id)) {
    $municipality_id = null;
}
$searchbox = $this->Form->create(null, array(
    'controller' => 'offers',
    'action' => 'search'));
$searchbox .= $this->Form->input('contains', array(
    'label' => 'Περιέχει:',
    'type' => 'text',
    'value' => $search_string,
    'class' => 'input-medium',
    'title' => 'Κείμενο που περιέχεται σε προσφορά ή επιχείρηση'));
$searchbox .= $this->Form->label('Offer.municipality',
                                 'Η επιχείριση βρίσκεται στο δήμο:');
$searchbox .= $this->Form->select('municipality', $municipalities, array(
    'label' => 'sdf',
    'value' => $municipality_id,
    'title' => 'Περιορισμός αποτελεσμάτων σε επιχειρήσεις αυτού του δήμου'));
$searchbox .= $this->Form->submit('Αναζήτηση');
$searchbox .= $this->Form->end();

?>
    <div class="span3">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">Αναζητηση</li>
              <li><?php echo $searchbox ?></li>
              <?php if (isset($spam_link)) echo $spam_link ?>
              <li class="nav-header">Ειδη Προσφορων</li>
              <li><?php echo $happyhour, " ($happyhour_count)", $happyhour_rss ?></li>
              <li><?php echo $coupons, " ($coupons_count)", $coupons_rss?></li>
              <li><?php echo $limited, " ($limited_count)", $limited_rss ?></li>
              <li class="nav-header">Κατηγοριες Προσφορων</li>
              <?php echo $html; ?>
            </ul>
          </div><!--/.well -->
    </div><!--/span-->
