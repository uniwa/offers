<?php
// set channel metadata
$this->set('channelData', array(
    'title' => __("Τελευταίες προσφορές"),
    'link' => $this->Html->url('/', true),
    'description' => __("Κατηγορία \"{$this->params['action']}\""),
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
