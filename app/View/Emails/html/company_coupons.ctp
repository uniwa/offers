<?php
echo "<p>Η προσφορά κουπονιών «{$offer_title}» συνέλεξε τα ακόλουθα κουπόνια:</p>";

$length = count($coupons);

$i = 1;
$s = 'style="padding: 0.2em;"';
echo <<<EOTS
<table>
    <thead>
        <tr>
            <th {$s}>Α/Α</th>
            <th {$s}>Κωδικός κουπονιού</th>
        </tr>
    </thead>
    <tbody>
EOTS;

foreach($coupons as $c) {
    $serial = $c['Coupon']['serial_number'];
    echo "<tr><td $s>$i</td><td $s><pre>$serial</pre></td></tr>";
    ++$i;
}

echo '</tbody></table>';

