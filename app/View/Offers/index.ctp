<?php

echo 'Προσφορές<br/><br/>';

foreach ($offers as $offer) {

    echo $this->Html->link($offer['Offer']['title'],
                           array('controller' => 'offers',
                                 'action' => 'view',
                                 $offer['Offer']['id']));
}
