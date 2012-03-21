<?php

echo $this->Form->create('Offer', array(
                        'type' => 'POST',
                        'enctype' => 'multipart/form-data'
                        ));

echo $this->Form->input('title', array(
                        'label' => 'Τίτλος',
                        'type' => 'text'
                       ));
echo $this->Form->input('description', array(
                        'label' => 'Περιγραφή',
                        'type' => 'text'
                       ));
echo $this->Form->input('offer_category_id', array(
                        'label' => 'Κατηγορία Προσφοράς'
                       ));
echo $this->Form->input('offer_type_id', array(
                        'label' => 'Είδος Προσοράς'
                       ));
echo $this->Form->input('starting', array(
                        'label' => 'Ημερ/νία & Ώρα έναρξης προσφοράς',
                        'separator' => ' ',
                        'dateFormat' => 'DMY',
                        'minYear' => date('Y'),
                        'maxYear' => date('Y') + 1,
                        'orderYear' => 'asc',
                        'timeFormat' => 24,
                        'interval' => 15
                       ));
echo $this->Form->input('ending', array(
                        'label' => 'Ημερ/νία & Ώρα λήξης προσφοράς',
                        'separator' => ' ',
                        'dateFormat' => 'DMY',
                        'minYear' => date('Y'),
                        'maxYear' => date('Y') + 1,
                        'orderYear' => 'asc',
                        'timeFormat' => 24,
                        'interval' => 15
                       ));

// this field should be available to the user only when
// the offer_category is "happyhour"
echo 'Happy Hour only<br/>';?>

<?php 

echo $this->element( "work_hours", array(
    "work_hour_count" => $work_hour_count,
    "timeFormat" => 24,
    //"interval" => 15 //default
    "header" => array('Ημέρα', 'Ώρα Έναρξης', 'Ώρα Λήξης', 'Επιλογή'),
    "table_class" => "table table-striped",
    //"input_class" => span3, //default
    //"input_label" => null, //default
    //"staring_time_label" => null, //default
    //"ending_time_lebel" => null, //default
) );?>









<?php echo 'Happy Hour only end<br/>';

echo $this->Form->input('expiration_date', array(
                        'label' => 'Η προσφορά ισχύει μέχρι',
                        'type' => 'date',
                        'separator' => ' ',
                        'dateFormat' => 'DMY',
                        'minYear' => date('Y'),
                        'maxYear' => date('Y') + 5,
                        'orderYear' => 'asc'
                       ));
echo $this->Form->input('total_quantity', array(
                        'label' => 'Συνολικός αριθμός διαθέσιμων κουπονιών',
                        'empty' => 'Δεν έχει κουπόνια'
                       ));
echo $this->Form->input('tags', array('label' => 'Λέξεις κλειδιά'));
echo $this->Form->input('Image.0', array(
                        'label' => 'Φωτογραφία',
                        'type' => 'file'
                       ));

echo $this->Form->input('Image.1', array(
                        'label' => 'Φωτογραφία',
                        'type' => 'file'
                       ));
echo $this->Form->input('id', array('type' => 'hidden'));
echo $this->Form->end('Ανάρτηση');
