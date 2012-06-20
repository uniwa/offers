<div class="users form">
<?php echo $this->Form->create('User');?>
<?php echo $this->Session->flash(); ?>
    <fieldset class = "login-fieldset">
        <legend><?php echo __('Σύνδεση Χρήστη'); ?></legend>
    <?php
        echo $this->Form->input('username', array( 'label' => 'Όνομα χρήστη', 'type' => 'text', 'class' => 'span9'));
        echo $this->Form->input('password', array( 'label' => 'Κωδικός χρήστη', 'class' => 'span9'));
    ?>
    </fieldset>
<?php echo $this->Form->end( array( 'class' => 'btn btn-primary' , 'label'=>'Είσοδος'));?>
</div>
<?php
echo $this->Html->link('Ξέχασα τον κωδικό μου', array(
    'controller' => 'users',
    'action' => 'request_passwd'));

