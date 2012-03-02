<h2>Εγγραφή νέου χρήστη</h2>
<div class="register form">
<?php
    echo $this->Session->flash('register');
    echo $this->Form->create('User', array(
                                        'action' => 'register',
                                        'type' => 'POST',
                                        'enctype' => 'multipart/form-data',
                                    )
                              );
?>
    <fieldset>
        <legend><?php echo __('Εγγραφή χρήστη'); ?></legend>
    <?php

        echo $this->Form->input('User.username', array( 'label' => 'Όνομα χρήστη' ,'type'=>'text', 'class'=>'span4') );
        echo $this->Form->input('User.password', array( 'label' => 'Κωδικός πρόσβασης', 'class'=>'span4'));
        echo $this->Form->input('User.repeat_password', array( 'label' => 'Επάναληψη κωδικού πρόσβασης', 'type' => 'password', 'class'=>'span4'));
        echo $this->Form->input('User.email', array( 'label' => 'e-mail', 'class'=>'span4','type'=>'text') );
        echo $this->Form->input('Company.name', array( 'label' => 'Όνομα επιχείρησης', 'class'=>'span4','type'=>'text') );
        echo $this->Form->input('Company.service_type', array( 'label' => 'Προσφερόμενη υπηρεσία', 'class'=>'span4','type'=>'text') );   
        echo $this->Form->input('Company.Image.0', array( 'label' => 'Λογότυπο', 'type' => 'file' ) );
        echo $this->Form->input('Company.Image.1', array( 'label' => 'Λογότυπο', 'type' => 'file' ) );
        echo $this->Form->input('Company.address', array( 'label' => 'Διεύθυνση', 'class'=>'span4') );
        echo $this->Form->input('Company.postalcode', array( 'label' => 'Ταχυδρομικός κώδικας', 'class'=>'span1') );
        echo $this->Form->input('Company.phone', array( 'label' => 'Τηλέφωνο', 'class'=>'span2','type'=>'text') );
        echo $this->Form->input('Company.fax', array( 'label' => 'Fax', 'class'=>'span2','type'=>'text') );
        echo $this->Form->input('Company.afm', array( 'label' => 'Α.Φ.Μ', 'class'=>'span3','type'=>'text') );
        echo $this->Form->input('Company.doy', array( 'label' => 'ΔΟΥ', 'class'=>'span4','type'=>'text') );


    ?>    
        <tr>
            Ωράριο λειτουργίας
        </tr>

        <table class="table table-bordered">
           
          <thead>
            <tr>
            <th>ΗΜΕΡΑ</th>
            <th>Ώρα έναρξης</th>
            <th>Ώρα λήξης</th>
            </tr>
          </thead>
          <tbody>
            <tr>
                <td>
                    <?php echo $this->Form->input( 'WorkHour.0.day_id', array( 'label'=>'Ημέρα' ));?>
                </td>
                <td>
                    <?php echo $this->Form->input( 'WorkHour.0.starting',
                            array( 'label'=>null, 'type'=>'time', 'timeFormat'=>24, 'interval'=>15, 'class'=>'span3' ));?>
                </td>
                <td>
                    <?php echo $this->Form->input( 'WorkHour.0.ending',
                            array( 'label'=>null, 'type'=>'time', 'timeFormat'=>24, 'interval'=>15, 'class'=>'span3'));?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo $this->Form->input( 'WorkHour.1.day_id', array( 'label'=>'Ημέρα' ));?>
                </td>
                <td>
                    <?php echo $this->Form->input( 'WorkHour.1.starting',
                            array( 'label'=>null, 'type'=>'time', 'timeFormat'=>24, 'interval'=>15 , 'class'=>'span3'));?>
                </td>
                <td>
                    <?php echo $this->Form->input( 'WorkHour.1.ending',
                            array( 'label'=>null, 'type'=>'time', 'timeFormat'=>24, 'interval'=>15, 'class'=>'span3'));?>
                </td>
            </tr>
          </tbody>
        </table>

    </fieldset>
<?php echo $this->Form->end(__('Υποβολή'));?>
</div>
