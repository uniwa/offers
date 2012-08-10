<?php

// Note that "Α/Α" is tabulated at 6 characters

echo <<<EOTS
Η προσφορά κουπονιών «{$offer_title}» συνέλεξε τα ακόλουθα κουπόνια:

   Α/Α   Κωδικός κουπονιού
EOTS;

$length = count($coupons);
$i = 1;

foreach($coupons as $c) {
    printf('%6s   %s\r\n', $i, $c['Coupon']['serial_number']);
    ++$i;
}

