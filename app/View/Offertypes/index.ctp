<?php

echo $this->Html->link('Προσθήκη τύπου προσφοράς.',
                       array('controller' => 'offertypes', 'action' => 'add'));

echo '<br/><br/>';

if (isset($results)) {
    foreach ($results as $otype) {
        echo $otype['OfferType']['name'].'<br/>';
    }
}
