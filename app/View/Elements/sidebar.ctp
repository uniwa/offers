<?php
$happyhour = $this->Html->link(
    'HappyHour',
    array('controller' => 'offers', 'action' => 'happyhour'),
    array('class' => '')
);

$happyhour_rss = $this->Html->link(
    ' ',
    array('controller' => 'offers', 'action' => 'happyhour.rss'),
    array('class' => 'rss-happyhour', 'title' => 'RSS feed for HappyHour offers')
);

$coupons = $this->Html->link(
    'Coupons',
    array('controller' => 'offers', 'action' => 'coupons'),
    array('class' => '')
);

$coupons_rss = $this->Html->link(
    '',
    array('controller' => 'offers', 'action' => 'coupons.rss'),
    array('class' => 'rss-coupons', 'title' => 'RSS feed for Coupon offers')
);

$limited = $this->Html->link(
    'Limited',
    array('controller' => 'offers', 'action' => 'limited'),
    array('class' => '')
);

$limited_rss = $this->Html->link(
    '',
    array('controller' => 'offers', 'action' => 'limited.rss'),
    array('class' => 'rss-limited', 'title' => 'RSS feed for limited offers')
);

if (isset($shows_spam)) {
    // if `shows_spam' has been set to true, then only spam offers are currently
    // being rendered, in which case a link to display non-spam offers should be
    // provided; if it has not been set at all then it is implied that the user
    // is not admin and no link should be provided at all
    $spam_link = $shows_spam
            ? $this->Html->link('Απόκρυψη των SPAM',
                                array('controller' => 'offers',
                                      'action' => 'index'))
            : $this->Html->link('Εμφάνιση των SPAM',
                                array('controller' => 'offers',
                                      'action' => 'spam'));
}

$html = '';
foreach($offer_categories as $id => $name) {
    $html .= "<li>";
    $html .= $this->Html->link($name, array(
        'controller' => 'offers', 'action' => 'category', $id));
    $html .= " ({$count_by_category[$id][0]})";
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
$searchbox = $this->Form->create(null, array(
    'controller' => 'offers',
    'action' => 'search'));
$searchbox .= $this->Form->input('search', array(
    'label' => '',
    'type' => 'text',
    'value' => $search_string,
    'class' => 'input-medium'));
$searchbox .= $this->Form->end();

?>
    <div class="span3">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">Αναζητηση</li>
              <li><?php echo $searchbox ?></li>
              <?php if (isset($spam_link)) echo $spam_link ?>
              <li class="nav-header">Ειδη Προσφορων</li>
              <li><?php echo $happyhour, $happyhour_rss ?></li>
              <li><?php echo $coupons, $coupons_rss?></li>
              <li><?php echo $limited, $limited_rss ?></li>
              <li class="nav-header">Κατηγοριες Προσφορων</li>
              <?php echo $html; ?>
            </ul>
          </div><!--/.well -->
    </div><!--/span-->
