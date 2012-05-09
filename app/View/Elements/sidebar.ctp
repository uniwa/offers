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
?>
    <div class="span3">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">Κατηγοριες Προσφορων</li>
              <li><?php echo $happyhour ?></li>
              <li><?php echo $coupons ?></li>
              <li><?php echo $limited ?></li>
              <li class="nav-header">Ειδη Προσφορων</li>
              <li><a href="#">Φαγητό</a></li>
              <li><a href="#">Διασκέδαση</a></li>
              <li><a href="#">Δραστηριότητες & Χόμπι</a></li>
              <li><a href="#">Υπηρεσίες</a></li>
              <li><a href="#">Ένδυση & Υπόδηση</a></li>
              <li><a href="#">Υγεία </a></li>
              <li><a href="#">Προϊόντα </a></li>
            </ul>
          </div><!--/.well -->
    </div><!--/span-->
