<?php

echo '<ul style="list-style-type: none; padding: 0;">';
foreach ($offers as $offer) {

    $offer_url = $app_url . $this->Html->url(array('controller' => 'offers',
                                                   'action' => 'view',
                                                   $offer['Offer']['id']));

    $offer_url = $this->Html->link($offer['Offer']['title'],
                                   $offer_url);

#echo FULL_BASE_URL.$this->Html->url(array('controller' => 'offers',
#                                        'action' => 'view',
#                                        $offer['Offer']['id']));
    echo <<<EOT

    <li>
        <div>
            <h3>{$offer_url}</h3>
            <p>Κατηγορία: {$offer['OfferCategory']['name']}</p>
            <p>Δημοσίευση: {$offer['Offer']['started']}</p>
        </div>
    </li>
EOT;

}
echo '</ul>';
