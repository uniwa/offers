<?php

foreach ($companies as $company) {
    $company = $company['Company'];

    echo 'Επιχείρηση '.$company['id'].'<br/>';

    if (isset($company['name']))
        echo 'Όνομα επιχείρησης : '.$company['name'].'<br/>';

    if (isset($company['logo']))
        echo 'Λογότυπο : '.$company['logo'].'<br/>';

    if (isset($company['address']))
        echo 'Διεύθυνση : '.$company['address'].'<br/>';

    if (isset($company['postal_code']))
        echo 'Ταχ. Κώδικας : '.$company['postal_code'].'<br/>';

    if (isset($company['phone']))
        echo 'Τηλέφωνο : '.$company['phone'].'<br/>';

    if (isset($company['fax']))
        echo 'Φαξ : '.$company['fax'].'<br/>';

    if (isset($company['service_type']))
        echo 'Είδος υπηρεσιών : '.$company['service_type'].'<br/>';

    if (isset($company['afm']))
        echo 'ΑΦΜ : '.$company['afm'].'<br/>';

    if (isset($company['doy']))
        echo 'ΔΟΥ : '.$company['doy'].'<br/>';

    if (isset($company['working_hours']))
        echo 'Ωράριο λειτουργίας : '.$company['working_hours'].'<br/>';

    echo '<br/>';
}
