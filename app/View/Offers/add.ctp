<?php

echo $this->Form->create(false, array('type' => 'file'));

foreach ($input_elements as $elem) {
    $elem_html = $this->Form->input($elem['title'], $elem['options']);
    echo $elem_html;
}

echo $this->element( "work_hours", array(
    "work_hour_count" => 0,
    "timeFormat" => 24,
    //"interval" => 15 //default
    "header" => array('Ημέρα', 'Ώρα Έναρξης', 'Ώρα Λήξης', 'Επιλογή'),
    "table_class" => "table table-striped",
    //"input_class" => span3, //default
    //"input_label" => null, //default
    //"staring_time_label" => null, //default
    //"ending_time_lebel" => null, //default
));

echo $this->Form->end('Αποθήκευση');
