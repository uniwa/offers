<?php

$comp = $company['Company'];

if ($this->Session->read('Auth.User.id') == $comp['user_id']) {
    echo $this->Html->link('Ανάρτηση Προσφοράς',
                           array('controller' => 'offers', 'action' => 'add'));
    echo '<br/>';

    echo $this->Html->link('Επεξεργασία στοιχείων επιχείρησης',
                           array('controller' => 'companies',
                           'action' => 'edit',
                           $company['Company']['id']));
    echo '<br/>';
}

echo 'Επιχείρηση '.$comp['id'].'<br/>';

if (isset($comp['name']))
    echo 'Όνομα επιχείρησης : '.$comp['name'].'<br/>';

if (isset($comp['address']))
    echo 'Διεύθυνση : '.$comp['address'].'<br/>';

if (isset($comp['postalcode']))
    echo 'Ταχ. Κώδικας : '.$comp['postalcode'].'<br/>';

if (isset($company['User']['email']))
    echo 'Email : '.$company['User']['email'].'<br/>';

if (isset($comp['phone']))
    echo 'Τηλέφωνο : '.$comp['phone'].'<br/>';

if (isset($comp['fax']))
    echo 'Φαξ : '.$comp['fax'].'<br/>';

if (isset($comp['service_type']))
    echo 'Είδος υπηρεσιών : '.$comp['service_type'].'<br/>';

if (isset($comp['afm']))
    echo 'ΑΦΜ : '.$comp['afm'].'<br/>';

if (isset($comp['working_hours']))
    echo 'Ωράριο λειτουργίας : '.$comp['working_hours'].'<br/>';

foreach ($company['Image'] as $image) {
    echo $this->Html->image('/images/view/'.$image['id']).'<br/>';
}
echo '<br/>';

// display Drafts only for the owner of this company
if ($this->Session->read('Auth.User.id') == $comp['user_id']) {
    if (empty($company['Offer']['Draft'])) {
        echo 'Δεν υπάρχουν αποθηκευμένες προσφορές.<br/>';
    } else {
        echo 'Αποθηκευμένες προσφορές:<br/>';
        foreach ($company['Offer']['Draft'] as $draft) {
            echo $this->Html->link($draft['title'],
                                   array('controller' => 'offers',
                                         'action' => 'view', $draft['id'])
                                  );
            echo '<br/>';
        }
    }
}

// display Active offers
if (empty($company['Offer']['Active'])) {
    echo 'Δεν υπάρχουν ενεργές προσφορές.<br/>';
} else {
    echo 'Ενεργές προσφορές:<br/>';
    foreach ($company['Offer']['Active'] as $active) {
        echo $this->Html->link($active['title'],
                               array('controller' => 'offers',
                                     'action' => 'view', $active['id'])
                              );
        echo '<br/>';
    }
}

// display Inactive offers
if (empty($company['Offer']['Inactive'])) {
    echo 'Δεν υπάρχουν παλαιότερες προσφορές.<br/>';
} else {
    echo 'Παλαιότερες προσφορές:<br/>';
    foreach ($company['Offer']['Inactive'] as $inactive) {
        echo $this->Html->link($inactive['title'],
                               array('controller' => 'offers',
                                     'action' => 'view', $inactive['id'])
                              );
        echo '<br/>';
    }
}
