<?php
$happyhour = $this->Html->link(
    'HappyHour',
    array('controller' => 'offers', 'action' => 'happyhour'),
    array('class' => '')
);

$coupons = $this->Html->link(
    'Coupons',
    array('controller' => 'offers', 'action' => 'coupons'),
    array('class' => '')
);

$limited = $this->Html->link(
    'Limited',
    array('controller' => 'offers', 'action' => 'limited'),
    array('class' => '')
);
$html = '';
foreach($offer_categories as $id => $name) {
    $html .= "<li>";
    $html .= $this->Html->link($name, array(
        'controller' => 'offers', 'action' => 'category', $id));
    $html .= "</li>";
}
?>
    <div class="span3">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">Κατηγοριες Προσφορων</li>
              <li><?php echo $happyhour ?></li>
              <li><?php echo $coupons ?></li>
              <li><?php echo $limited ?></li>
              <li class="nav-header">Ειδη Προσφορων</li>
              <?php echo $html; ?>
            </ul>
          </div><!--/.well -->
    </div><!--/span-->
