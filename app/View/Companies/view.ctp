<?php

$comp = $company['Company'];

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

echo '<br/>Προσφορές επιχείρησης:<br/>';

foreach ($company['Offer'] as $offer)
{
    echo $offer['title'].'<br/>';
}
