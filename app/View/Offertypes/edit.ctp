<?php

echo '<h2>Επεξεργασία τύπου προσφοράς</h2>';

echo $this->Form->create('OfferType', array('type' => 'POST'));
echo $this->Form->input('name', array('label' => 'Όνομα τύπου', 'type' => 'text'));
echo $this->Form->hidden('id');
echo $this->Form->end('Αποθήκευση');
