<?php

echo $this->Form->Create(null, array(
    'url' => array(
        'controller' => 'users',
        'action' => 'reset_passwd',
        $token),
    'method' => 'post',
    'class' => 'form-vertical'));

echo $this->Tb->input(array(
    'field' => 'User.password',
    'input' => $this->Form->password(
                    'User.password', array('class' => 'span4')
                ),
    'label' => 'Κωδικός πρόσβασης'
));

echo $this->Tb->input(array(
    'field' => 'User.repeat_password',
    'input' => $this->Form->password(
                    'User.repeat_password', array('class' => 'span4')
                ),
    'label' => 'Επάναληψη κωδικού πρόσβασης'
));

echo $this->Tb->button("Reset", array(
    "size" => "small"));
