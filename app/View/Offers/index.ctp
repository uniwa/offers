<?php
    echo $this->element('sidebar');
?>
<div class='span9'>
<?php
$html = '';
if (empty($offers)) {
    $html .= $this->element('alert', array(
        'type' => 'info',
        'label' => '<span class="label label-info">Offers</span>',
        'message' => 'Δεν υπάρχουν προσφορές'));
} else {
    //separate in blocks of 3
    $sep = 3;

    //offers
    foreach ($offers as $key => $offer) {
        $offer_type_id = $offer['Offer']['offer_type_id'];
        $tag_classes = array('info', 'warning', 'success');
        $tag_class = $tag_classes[$offer_type_id - 1];
        $tag_name = offer_type($offer_type_id);
        $title = $offers[$key]['Offer']['title'];
        $label = "<span class='label label-{$tag_class}'>{$tag_name}</span>";
        $description = "<p>{$offers[$key]['Offer']['description']}</p>";
        $html .=  $this->Html->link($title,
            array('action' => 'view', $offers[$key]['Offer']['id']));
        $html .= " ".$label;
        $html .= "<br /><i>{$offer['Offer']['modified']}</i>";
        $html .= $description;
    }
}
echo $html;
?>
<div class = 'pagination'>
<ul>
<?php
$html_page = $this->Paginator->numbers(array(
    'first' => 2,
    'last' => 2,
    'modulus' => 3,
    'separator' => ' ',
    'tag' => 'li',
    'ellipsis' => '<a href=" ">...</a>'));

echo $html_page;
?>
</ul>
</div>
</div>
