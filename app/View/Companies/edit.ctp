<?php

echo $this->Form->create(false, array(
                                    'url' => array(
                                        'controller' => 'companies',
                                        'action' => 'edit',
                                        $company['Company']['id']
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

<?php 
echo $this->element( "work_hours", array(
    "work_hour_count" => $company['Company']['work_hour_count'],
    "timeFormat" => 24,
    //"interval" => 15 //default
    "header" => array('Ημέρα', 'Ώρα Έναρξης', 'Ώρα Λήξης', 'Επιλογή'),
    "table_class" => "table table-striped",
    //"input_class" => span3, //default
    //"input_label" => null, //default
    //"staring_time_label" => null, //default
    //"ending_time_lebel" => null, //default
) );?>

<?php

foreach ($company['Image'] as $image) {
    echo $this->Html->image('/images/view/'.$image['id']);
    echo $this->Html->link('Διαγραφή',
                           array(
                                'controller' => 'images',
                                'action' => 'delete',
                                $image['id']),
                           array(),
                           'Να διαγραφεί η εικόνα;'
                          ).'<br/>';
}

echo $this->Form->input('Image.0', array(
                            'label' => 'Φωτογραφία',
                            'type' => 'file'
                       ));

echo $this->Form->input('Image.1', array(
                            'label' => 'Φωτογραφία',
                            'type' => 'file'
                       ));

echo $this->Form->hidden('User.id');
echo $this->Form->hidden('Company.id');
echo $this->Form->hidden('Company.user_id');
echo $this->Form->hidden('Company.afm');
echo $this->Form->end('Αποθήκευση');
echo $this->Html->link('Επιστροφή', array(
                       'controller' => 'companies',
                       'action' => 'view',
                       $company['Company']['id']));
