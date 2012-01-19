<?php

$comp = $company['Company'];

if ($this->Session->read('Auth.User.id') == $comp['user_id']) {
    echo $this->Html->link('Ανάρτηση Προσφοράς',
                           array('controller' => 'offers', 'action' => 'add'));
    echo '<br/>';
}

echo 'Επιχείρηση '.$comp['id'].'<br/>';

if (isset($comp['name']))
    echo 'Όνομα επιχείρησης : '.$comp['name'].'<br/>';

if (isset($comp['logo']))
    echo 'Λογότυπο : '.$comp['logo'].'<br/>';

if (isset($comp['address']))
    echo 'Διεύθυνση : '.$comp['address'].'<br/>';

if (isset($comp['postal_code']))
    echo 'Ταχ. Κώδικας : '.$comp['postal_code'].'<br/>';

if (isset($comp['phone']))
    echo 'Τηλέφωνο : '.$comp['phone'].'<br/>';

if (isset($comp['fax']))
    echo 'Φαξ : '.$comp['fax'].'<br/>';

if (isset($comp['service_type']))
    echo 'Είδος υπηρεσιών : '.$comp['service_type'].'<br/>';

if (isset($comp['afm']))
    echo 'ΑΦΜ : '.$comp['afm'].'<br/>';

if (isset($comp['doy']))
    echo 'ΔΟΥ : '.$comp['doy'].'<br/>';

if (isset($comp['working_hours']))
    echo 'Ωράριο λειτουργίας : '.$comp['working_hours'].'<br/>';

if (isset($company['Image']['id']))
    echo $this->Html->image('/images/view/'.$company['Image']['id']);

echo '<br/>Προσφορές επιχείρησης:<br/>';

foreach ($company['Offer'] as $offer)
{
    echo $this->Html->link($offer['title'], array(
                                                'controller' => 'offers',
                                                'action' => 'view',
                                                $offer['id'])
                          ).'<br/>';
}
