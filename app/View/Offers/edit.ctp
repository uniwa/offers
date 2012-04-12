<?php
$offer_type_id = $this->request->data['Offer']['offer_type_id'];
switch($offer_type_id){
    case 1:
        $label_class = 'label-info';
        break;
    case 2:
        $label_class = 'label-warning';
        break;
    case 3:
        $label_class = 'label-success';
        break;
}
$label_text = offer_type($offer_type_id);
echo "<p><span class='label {$label_class}'>{$label_text}</span></p>";

$elem_html = $this->Form->create(false, array('type' => 'file'));

foreach ($input_elements as $elem) {
    $elem_html .= $this->Form->input($elem['title'], $elem['options']);
}

$elem_html .= $this->element( "work_hours", array(
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

$elem_html .= $this->Form->end('Αποθήκευση');

echo $elem_html;
