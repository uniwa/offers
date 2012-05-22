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

$elem_html = $this->Form->create(false);

foreach ($input_elements as $elem) {
    $elem_html .= $this->Form->input($elem['title'], $elem['options']);
}

if (isset($work_hours))
    $elem_html .= $this->element('work_hours', $work_hours);

$elem_html .= $this->Form->end('Αποθήκευση');
echo $elem_html;
