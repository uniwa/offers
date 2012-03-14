<h2>Εγγραφή νέου χρήστη</h2>
<div class="register form">
<?php
    echo $this->Session->flash('register');
    echo $this->Form->create(false, array(
                                        'action' => 'register',
                                        'type' => 'POST',
                                        'enctype' => 'multipart/form-data',
                                    )
                              );
?>
    <fieldset>
        <legend><?php echo __('Εγγραφή χρήστη'); ?></legend>
<?php

    echo $this->Form->input('User.username', array(
                            'label' => 'Όνομα χρήστη',
                            'type'  => 'text',
                            'class' => 'span4'));
    echo $this->Form->input('User.password', array(
                            'label' => 'Κωδικός πρόσβασης',
                            'class' => 'span4'));
    echo $this->Form->input('User.repeat_password', array(
                            'label' => 'Επάναληψη κωδικού πρόσβασης',
                            'type'  => 'password',
                            'class' => 'span4'));
    echo $this->Form->input('User.email', array(
                            'label' => 'e-mail',
                            'type'  => 'text',
                            'class' => 'span4'));
    echo $this->Form->input('Company.name', array(
                            'label' => 'Όνομα επιχείρησης',
                            'type'  => 'text',
                            'class' => 'span4'));
    echo $this->Form->input('Company.phone', array(
                            'label' => 'Τηλέφωνο',
                            'type'  => 'text',
                            'class' => 'span3'));
    echo $this->Form->input('Company.afm', array(
                            'label' => 'Α.Φ.Μ',
                            'type'  => 'text',
                            'class' => 'span3'));

?>

    </fieldset>
<?php echo $this->Form->end(__('Υποβολή'));?>
</div>
