<?php

$style_listing = $this->Html->style(array('list-style-type' => 'none',
                                          'padding' => 0));
$style_heading = $this->Html->style(array('margin-bottom' => 0));

echo "<ul style=\"$style_listing\">";
foreach ($offers as $offer) {

    $offer_url = $app_url . $this->Html->url(array('controller' => 'offers',
                                                   'action' => 'view',
                                                   $offer['Offer']['id']));

    $offer_url = $this->Html->link($offer['Offer']['title'],
                                   $offer_url);

    $offer_date = date('d/m/Y H:i', strtotime($offer['Offer']['started']));
    $offer_decription = $offer['Offer']['description'];
    $offer_type = $offer_types[$offer['Offer']['offer_type_id']];
    $offer_category = $offer['OfferCategory']['name'];

    echo <<<EOT

    <li>
        <h3 style="{$style_heading}">{$offer_url}</h3>
        <ul style="{$style_listing}">
            <li>{$offer_date}</li>
            <li>{$offer_type} κατηγορίας &laquo;{$offer_category}&raquo;</li>
            <li>{$offer_decription}</li>
        </ul>
    </li>
EOT;

}
echo '</ul>';
