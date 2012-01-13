<h2>Εγγραφή νέου χρήστη</h2>
<div class="register form">
<?php echo $this->Form->create('User');?>
    <fieldset>
        <legend><?php echo __('Εγγραφή χρήστη'); ?></legend>
    <?php
        echo $this->Form->input('username', array( 'label' => 'Όνομα χρήστη'));
        echo $this->Form->input('password', array( 'label' => 'Κωδικός πρόσβασης'));
    ?>
    </fieldset>
<?php echo $this->Form->end(__('Υποβολή'));?>
</div>
