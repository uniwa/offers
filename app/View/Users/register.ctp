<h2>Εγγραφή νέου χρήστη</h2>
<div class="register form">
<?php
    echo $this->Session->flash();
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
    echo $this->Tb->input(array(
        'field' => 'User.username',
        'input' => $this->Form->text(
                        'User.username', array('class' => 'span4')
                    ),
        'label' => 'Όνομα χρήστη'
    ));

    echo $this->Tb->input(array(
        'field' => 'User.password',
        'input' => $this->Form->text(
                        'User.password', array('class' => 'span4')
                    ),
        'label' => 'Κωδικός πρόσβασης'
    ));

    echo $this->Tb->input(array(
        'field' => 'User.repeat_password',
        'input' => $this->Form->text(
                        'User.repeat_password', array('class' => 'span4')
                    ),
        'label' => 'Επάναληψη κωδικού πρόσβασης'
    ));

    echo $this->Tb->input(array(
        'field' => 'User.email',
        'input' => $this->Form->text(
                        'User.email', array('class' => 'span4')
                    )
    ));

    echo $this->Tb->input(array(
        'field' => 'Company.name',
        'input' => $this->Form->text(
                        'Company.name', array('class' => 'span4')
                    ),
        'label' => 'Όνομα επιχείρησης'
    ));

    echo $this->Tb->input(array(
        'field' => 'Company.phone',
        'input' => $this->Form->text(
                        'Company.phone', array('class' => 'span4')
                    ),
        'label' => 'Τηλέφωνο επιχείρησης'
    ));

    echo $this->Tb->input(array(
        'field' => 'Company.afm',
        'input' => $this->Form->text(
                        'Company.afm', array('class' => 'span4')
                    ),
        'label' => 'Α.Φ.Μ. επιχείρησης'
    ));
?>
    </fieldset>
<?php
    echo $this->Tb->button(
            __('Υποβολή'),
            array(
                'style' => 'primary',
                'size' => 'small'
            )
        );
?>
</div>
