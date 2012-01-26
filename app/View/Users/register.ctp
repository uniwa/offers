<h2>Εγγραφή νέου χρήστη</h2>
<div class="register form">
<?php
    echo $this->Session->flash('register');
    echo $this->Form->create('User', array(
                                        'action' => 'register',
                                        'type' => 'POST',
                                        'enctype' => 'multipart/form-data')
                              );
?>
    <fieldset>
        <legend><?php echo __('Εγγραφή χρήστη'); ?></legend>
    <?php
        echo $this->Form->input('User.username', array( 'label' => 'Όνομα χρήστη'));
        echo $this->Form->input('User.password', array( 'label' => 'Κωδικός πρόσβασης'));
        echo $this->Form->input('User.repeat_password', array( 'label' => 'Επάναληψη κωδικού πρόσβασης', 'type' => 'password'));
        echo $this->Form->input('User.email', array( 'label' => 'e-mail') );
        echo $this->Form->input('Company.name', array( 'label' => 'Όνομα επιχείρησης') );
        echo $this->Form->input('Company.service_type', array( 'label' => 'Προσφερόμενη υπηρεσία') );
        echo $this->Form->input('Company.image', array( 'label' => 'Λογότυπο', 'type' => 'file' ) );
        echo $this->Form->input('Company.address', array( 'label' => 'Διεύθυνση') );
        echo $this->Form->input('Company.postalcode', array( 'label' => 'Ταχυδρομικός κώδικας') );
        echo $this->Form->input('Company.phone', array( 'label' => 'Τηλέφωνο') );
        echo $this->Form->input('Company.fax', array( 'label' => 'Fax') );
        echo $this->Form->input('Company.afm', array( 'label' => 'Α.Φ.Μ') );
        echo $this->Form->input('Company.doy', array( 'label' => 'ΔΟΥ') );

    ?>
        <table boreder="1">
            <tr>
                Ωράριο λειτουργίας
            </tr>

            <tr>
            <th>ΗΜΕΡΑ</th>
            <th>Ώρα έναρξης</th>
            <th>Ώρα λήξης</th>
            </tr>
            <tr>
                <td>
                    <?php echo $this->Form->input( 'Company.WorkingHour.day_id', array( 'label'=>'Ημέρα' ));?>
                </td>
                <td>
                    <?php echo $this->Form->input( 'Company.WorkingHour.starting', 
                            array( 'label'=>'Ωρα', 'type'=>'time', 'timeFormat'=>24, 'interval'=>15 ));?>
                </td>
                <td>
                    <?php echo $this->Form->input( 'Company.WorkingHour.ending', 
                            array( 'label'=>'Ωρα', 'type'=>'time', 'timeFormat'=>24, 'interval'=>15));?>
                </td>
        </table>
    
    </fieldset>
<?php echo $this->Form->end(__('Υποβολή'));?>
</div>
