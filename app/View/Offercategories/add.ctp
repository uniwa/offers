<?php

echo '<h2>Προσθήκη κατηγορίας προσφοράς</h2>';

echo $this->Form->create('OfferCategory', array('type' => 'POST'));
echo $this->Form->input('name', array('label' => 'Όνομα κατηγορίας', 'type' => 'text'));
echo $this->Form->end('Αποθήκευση');

echo $this->Html->link('Επιστροφή',
                       array('controller' => 'offercategories', 'action' => 'index'));
