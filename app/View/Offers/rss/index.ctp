<?php
// available offers
$offer_types = array(
    'coupons' => 'προσφορές με κουπόνια',
    'limited' => 'απλές προσφορές',
    'happyhour' => 'περιοδικές προσφορές');

// set rss description
if (in_array($this->params['action'], array_keys($offer_types))) {
    $desc = __("Κατηγορία \"{$offer_types[$this->params['action']]}\"");
} else {
    $desc = __("από ειδικές κατηγορίες");
}

// set channel metadata
$this->set('channelData', array(
    'title' => __("Τελευταίες προσφορές"),
    'link' => $this->Html->url('/', true),
    'description' => $desc,
    'language' => 'en-us'));

App::uses('Sanitize', 'Utility');

foreach ($offers as $offer) {
    $link = array(
        'controller' => 'offers',
        'action' => 'view',
        $offer['Offer']['id']
    );

    $body = $this->Text->stripLinks($offer['Offer']['description']);
    $body = Sanitize::stripAll($body);

    echo $this->Rss->item(
        array(),
        array(
            'title' => $offer['Offer']['title'],
            'link' => $link,
            'guid' => array('url' => $link, 'isPermaLink' => 'true'),
            'description' => $body,
            'pubDate' => $offer['Offer']['created']
    ));
}
