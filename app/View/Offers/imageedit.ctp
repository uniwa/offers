<?php
$html = '';
$html .= "<p>{$offer['Offer']['title']}</p>";
$html .= "<p>Διαχείριση εικόνων προσφοράς</p>";

if (!empty($offer['Image'])) {
    foreach ($offer['Image'] as $image) {
            $html .= "<div class='image_frame'>";
            $html .= $this->Html->image('/images/thumb/'.$image['id']);
            $link_delete = $this->Html->link('διαγραφή', array(
                'controller' => 'images', 'action' => 'delete', $image['id']),
                null, 'Η εικόνα θα διαγραφεί. Θέλετε να συνεχίσετε;');
            $html .= "<p>$link_delete</p></div>";
    }
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
