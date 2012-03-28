<?php

echo '<h2>Επεξεργασία κατηγορίας προσφοράς</h2>';

echo $this->Form->create('OfferCategory', array('type' => 'POST'));
echo $this->Form->input('name', array('label' => 'Όνομα τύπου', 'type' => 'text'));
echo $this->Form->hidden('id');
echo $this->Form->end('Αποθήκευση');

echo $this->Html->link('Επιστροφή',
                       array('controller' => 'offercategories', 'action' => 'index'));
