<?php

echo $this->Html->link('Προσθήκη.',
                       array('controller' => 'offercategories', 'action' => 'add'));

echo '<br/><br/>';

if (isset($results)) {
    foreach ($results as $ocategory) {
        echo $ocategory['OfferCategory']['name'].' ';
        echo $this->Html->link('Επεξεργασία',
                               array('controller' => 'offercategories',
                                     'action' => 'edit',
                                     $ocategory['OfferCategory']['id'])
                            ).' ';
        echo $this->Html->link('Διαγραφή',
                               array('controller' => 'offercategory',
                                     'action' => 'delete',
                                     $ocategory['OfferCategory']['id']),
                               array(),
                               'Να διαγραφεί η κατηγορία προσφοράς '
                                    .$ocategory['OfferCategory']['name'].';'
                            ).'<br/>';
    }
}
