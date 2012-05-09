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
    // TODO: make this a f***** list - stop the <br/> abuse
    // (when dealing with layout)
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

        // print tags as links if available
        if ($offer['Offer']['tags'] == NULL)
            continue;

        $tags = explode(' ', $offer['Offer']['tags']);
        $tag_num = count($tags);
        $tag_counter = 0;

        $html .= "<p>Tags: ";
        foreach ($tags as $tag) {
            $html .= $this->Html->link(
                $tag,
                array('controller' => 'offers', 'action' => 'tag', $tag)
            );

            // this is the only (and horrible) solution
            // because CakePHP is a lie and does not provide a
            // template engine and thus no mechanism to make this
            // intuitive
            $tag_counter++;
            if ($tag_counter !== $tag_num)
                $html .= ", ";
        }
        $html .= "</p>";
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
