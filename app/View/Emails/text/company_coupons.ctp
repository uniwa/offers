<?php
echo <<<EOTS
Η προσφορά κουπονιών «{$offer_title}» συνέλεξε τα ακόλουθα κουπόνια:

   Α/Α   Κωδικός κουπονιού
EOTS;

$length = count($coupons);
$i = 1;

foreach($coupons as $coupon => $dump) {
    printf('%6s   %s', $i, $coupon);
    ++$i;
}

