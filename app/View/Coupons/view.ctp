<div class="coupon-info">
    <h4>Κουπόνι</h4>
    <ul class="unstyled">
<?php
    echo "<li>Τίτλος προσφοράς: {$coupon['Offer']['title']}</li>";
    echo "<li>Κωδικός κουπονιού: {$coupon['Coupon']['serial_number']}</li>";
    echo "<li>Ημ/νία δέσμυσης: {$coupon['Coupon']['created']}</li>";
    echo "<li>Στοιχεία σπουδαστή: {$coupon['Student']['firstname']}"
        . " {$coupon['Student']['lastname']}</li>";
?>
    </ul>
</div>

<div class="company-info">
    <h4>Στοιχεία επιχείρησης</h4>
    <ul class="unstyled">
<?php
    echo "<li>Όνομα: {$coupon['Offer']['Company']['name']}</li>";
    echo "<li>Διεύθυνση: {$coupon['Offer']['Company']['address']}"
        . ", {$coupon['Offer']['Company']['postalcode']}</li>";
    echo "<li>Στοιχεία επικοινωνίας";
        echo "<ul>";
            echo "<li>{$coupon['Offer']['Company']['phone']}</li>";
            echo "<li>{$coupon['Offer']['Company']['fax']}</li>";
        echo "</ul>";
    echo "</li>";
?>
    </ul>
</div>

