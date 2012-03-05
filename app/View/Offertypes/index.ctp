<?php

echo $this->Html->link('Προσθήκη.',
                       array('controller' => 'offertypes', 'action' => 'add'));

echo '<br/><br/>';

if (isset($results)) {
    foreach ($results as $otype) {
        echo $otype['OfferType']['name'].' ';
        echo $this->Html->link('Επεξεργασία',
                               array('controller' => 'offertypes',
                                     'action' => 'edit',
                                     $otype['OfferType']['id'])
                            ).' ';
        echo $this->Html->link('Διαγραφή',
                               array('controller' => 'offertypes',
                                     'action' => 'delete',
                                     $otype['OfferType']['id']),
                               array(),
                               'Να διαγραφεί ο τύπος προσφοράς '
                                    .$otype['OfferType']['name'].';'
                            ).'<br/>';
    }
}
