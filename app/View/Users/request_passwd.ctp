<?php

echo $this->Form->Create('User', array(
    'action' => 'request_passwd',
    'method' => 'post',
'class' => 'form-horizontal'));

echo $this->Tb->input('User.email', array(
    'field' => 'User.email',
    'input' => $this->Form->text(
                    'User.email', array('class' => 'span4')
                )
));

echo $this->Tb->button("Reset", array(
    "size" => "small"));
