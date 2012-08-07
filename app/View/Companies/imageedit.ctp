<?php
// duplicate code - TODO: make element to share between company/offer imagedit
$html = '';
$html .= "<p>Διαχείριση εικόνων επιχείρησης</p>";

if (!empty($company['Image'])) {
    foreach ($company['Image'] as $image) {
            $html .= "<div class='image_frame'>";
            $html .= $this->Html->image('/images/thumb/'.$image['Image']['id']);
            $link_delete = $this->Html->link('διαγραφή <i class="icon-trash icon-white"></i>', array(
                'controller' => 'images', 'action' => 'delete', $image['Image']['id']),
                array('escape' => false, 'class' => 'btn btn-mini btn-danger'),
                'Η εικόνα θα διαγραφεί. Θέλετε να συνεχίσετε;');
            $html .= "<p>$link_delete</p></div>";
    }
} else {
    $html .= "Δεν υπάρχουν εικόνες για αυτή την επιχείρηση.";
}

if (isset($input_elements)) {
    $html .= $this->Form->create(false, array('type' => 'file'));
    foreach ($input_elements as $elem)
        $html .= $this->Form->input($elem['title'], $elem['options']);
    $options = array(
        'label' => 'Προσθήκη',
        'class' => 'btn btn-success',
    );
    $html .= $this->Form->end($options);
}
echo $html;
