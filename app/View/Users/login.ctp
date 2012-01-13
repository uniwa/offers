<div class="users form">
<?php echo $this->Session->flash('auth'); ?>
<?php echo $this->Form->create('User');?>
    <fieldset>
        <legend><?php echo __('Σύνδεση Χρήστη'); ?></legend>
    <?php
        echo $this->Form->input('username', array( 'label' => 'Όνομα χρήστη'));
        echo $this->Form->input('password', array( 'label' => 'Κωδικός χρήστη'));
    ?>
    </fieldset>
<?php echo $this->Form->end(__('Είσοδος'));?>
</div>

