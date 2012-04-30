<?php
$html = '';
$html .= "<p>{$offer['Offer']['title']}</p>";
$html .= "<p>Διαχείριση εικόνων προσφοράς</p>";

if (!empty($offer['Image'])) {
    foreach ($offer['Image'] as $image)
        if ($image['image_category_id'] == IMG_THUMB)
            $html .= $this->Html->image('/images/view/'.$image['id']);
} else {
    $html .= "Δεν υπάρχουν εικόνες για αυτή την προσφορά";
}

if (isset($input_elements)) {
    $html .= $this->Form->create(false, array('type' => 'file'));
    foreach ($input_elements as $elem)
        $html .= $this->Form->input($elem['title'], $elem['options']);
    $html .= $this->Form->end('Προσθήκη');
}
echo $html;
