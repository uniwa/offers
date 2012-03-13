<?php

echo $this->Form->create(false, array(
                                    'url' => array(
                                        'controller' => 'companies',
                                        'action' => 'edit', $company['Company']['id']
                                    ),
                                    'type' => 'POST',
                                    'enctype' => 'multipart/form-data',
                                 ));

echo $this->Form->input('Company.name', array(
                        'label' => 'Όνομα επιχείρησης',
                        'class' => 'span4',
                        'type'  => 'text'
                       ));
echo $this->Form->input('Company.service_type', array(
                        'label' => 'Προσφερόμενη υπηρεσία',
                        'class' => 'span4',
                        'type'  => 'text'
                       ));
echo $this->Form->input('Company.municipality_id', array(
                        'label' => 'Δήμος',
                        'type'  => 'select'
                       ));
echo $this->Form->input('Company.address', array(
                        'label' => 'Διεύθυνση',
                        'class' => 'span4',
                        'type'  => 'text',
                       ));
echo $this->Form->input('Company.postalcode', array(
                        'label' => 'Ταχυδρομικός κώδικας',
                        'class' => 'span1',
                        'type'  => 'text'
                       ));
echo $this->Form->input('User.email', array(
                        'label' => 'E-mail',
                        'class' => 'span4',
                        'type'  => 'text'
                       ));
echo $this->Form->input('Company.phone', array(
                        'label' => 'Τηλέφωνο',
                        'class' => 'span2',
                        'type'  => 'text'
                       ));
echo $this->Form->input('Company.fax', array(
                        'label' => 'Fax',
                        'class' => 'span2',
                        'type'  => 'text'
                       ));
?>

Ωράριο λειτουργίας

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ΗΜΕΡΑ</th>
            <th>Ώρα έναρξης</th>
            <th>Ώρα λήξης</th>
        </tr>
    </thead>
    <tbody>
<?php

    for ($i = 0; $i < count($company['WorkHour']); $i++) {
        echo '<tr>';

        echo $this->Form->hidden('WorkHour.'.$i.'.id');
        echo $this->Form->hidden('WorkHour.'.$i.'.company_id');

        echo '<td>'.
             $this->Form->input('WorkHour.'.$i.'.day_id',
                                array('label' => 'Ημέρα')).
             '</td>';

        echo '<td>'.
             $this->Form->input('WorkHour.'.$i.'.starting',
                                array(
                                    'label' => null,
                                    'type'=>'time',
                                    'timeFormat'=>24,
                                    'interval'=>15,
                                    'class'=>'span3'
                                )).
             '</td>';

        echo '<td>'.
             $this->Form->input('WorkHour.'.$i.'.ending',
                                array(
                                    'label' => null,
                                    'type'=>'time',
                                    'timeFormat'=>24,
                                    'interval'=>15,
                                    'class'=>'span3'
                                )).
             '</td>';

        echo '</tr>';
    }

?>
    </tbody>
</table>

<?php
echo $this->Form->hidden('User.id');
echo $this->Form->hidden('Company.id');
echo $this->Form->hidden('Company.user_id');
echo $this->Form->hidden('Company.afm');
echo $this->Form->end('Αποθήκευση');
echo $this->Html->link('Επιστροφή', array(
                       'controller' => 'companies',
                       'action' => 'view',
                       $company['Company']['id']));
