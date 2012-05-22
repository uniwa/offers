<?php

$coupon_url = $this->Html->url(array(
    'controller' => 'coupons', 'action' => 'view', $coupon_id), true);
$coupon_url = $this->Html->link($coupon_uuid, $coupon_url);

$offer_url = $this->Html->url(array(
    'controller' => 'offers', 'action' => 'view', $offer_id), true);
$offer_url = $this->Html->link($offer_url, $offer_url);


// get all keys in `Company' as prefixed variables to avoid (future) collisions
extract($company['Company'], EXTR_PREFIX_ALL | EXTR_REFS, 'comp');

$company_url = $this->Html->url(array(
    'controller' => 'companies', 'action' => 'view', $comp_id), true);
$company_url = $this->Html->link($company_url, $company_url);

echo <<<EOF
<p>Το παρόν μήνυμα στέλνεται ως επιβεβαίωση δέσμευσης κουπονιού για την προσφορά «{$offer_title}».</p>

<p>Κωδικός κουπονιού: $coupon_url</p>

<p>Σελίδα προσφοράς: $offer_url</p>

<p>Σελίδα επιχείρησης: $company_url</p>


<p>Στοιχεία επιχείρησης</p>
<ul style="{$this->Html->style(array('list-style-type' => 'none'), true)}">
    <li>Επωνυμία: $comp_name</li>

    <li>Οδός: $comp_address</li>
    <li>ΤΚ: $comp_postalcode</li>
    <li>Δήμος: $municipality</li>
    <li>Νομός: $county</li>

    <li>Τηλέφωνο: $comp_phone</li>
    <li>Fax: $comp_fax</li>
</ul>
EOF;
