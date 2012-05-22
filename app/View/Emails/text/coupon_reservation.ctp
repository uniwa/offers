<?php

$offer_url = $this->Html->url(array(
    'controller' => 'offers', 'action' => 'view', $offer_id), true);

// get all keys in `Company' as variables, prefixed to avoid (future) collisions
extract($company['Company'], EXTR_PREFIX_ALL | EXTR_REFS, 'comp');

$company_url = $this->Html->url(array(
    'controller' => 'companies', 'action' => 'view', $comp_id), true);

// using heredoc to ensure where newlines appear
echo <<<EOF
Το παρόν μήνυμα στέλνεται ως επιβεβαίωση δέσμευσης κουπονιού για την προσφορά «{$offer_title}».

Κωδικός κουπονιού: $coupon_uuid

Σελίδα προσφοράς: $offer_url

Σελίδα επιχείρησης: $company_url


Στοιχεία επιχείρησης
	Επωνυμία: $comp_name

	Οδός: $comp_address
	ΤΚ: $comp_postalcode
	Δήμος: $municipality
	Νομός: $county

	Τηλέφωνο: $comp_phone
	Fax: $comp_fax
EOF;
