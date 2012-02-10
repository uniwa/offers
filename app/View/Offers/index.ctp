<?php
echo 'Προσφορές<br/><br/>';

echo $this->Paginator->numbers(array('first' => 2, 'last' => 2));

foreach ($offers as $offer) {
    echo '<li/>';
    echo $this->Html->link($offer['Offer']['title'],
                           array('controller' => 'offers',
                                 'action' => 'view',
                                 $offer['Offer']['id']));
}
echo '<br>';
