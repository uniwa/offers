<?php

$style_listing = $this->Html->style(array('list-style-type' => 'none',
                                          'padding' => 0));
$style_heading = $this->Html->style(array('margin-bottom' => 0));

foreach ($offers as $offer) {

    $offer_url = $app_url . $this->Html->url(array('controller' => 'offers',
                                                   'action' => 'view',
                                                   $offer['Offer']['id']));

    $offer_date = date($published_format,
                       strtotime($offer['Offer']['started']));

    $offer_decription = $offer['Offer']['description'];
    $offer_type = $offer_types[$offer['Offer']['offer_type_id']];
    $offer_category = $offer['OfferCategory']['name'];

    echo <<<EOT

{$offer['Offer']['title']}
{$offer_url}
{$offer_date}
{$offer_type} κατηγορίας «{$offer_category}»
{$offer_decription}

EOT;

}

