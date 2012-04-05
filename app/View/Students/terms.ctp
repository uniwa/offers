<?php
echo "ΟΡΟΙ ΧΡΗΣΗΣ";

echo $this->Form->create();
echo $this->Form->input( 'accept',  array( 'label'=>'Αποδέχομαιτους όρους χρήσης', 'type'=>'checkbox' ) );
echo $this->Form->end('Συνέχεια');
