<?php
echo $this->element('terms');

// Display form to accept terms if terms not accepted
if ($this->Session->read( 'Auth.User.role') === ROLE_STUDENT) { //TODO set this in controller
    if (!$terms_accepted) {
        echo $this->Form->create();
        echo $this->Form->input('accept', array('label'=>'Αποδέχομαι τους όρους χρήσης', 'type'=>'checkbox'));
        echo $this->Form->end('Συνέχεια');
    }
}
