<?php
    echo $this->Html->css('leaflet');
    echo $this->Html->script('leaflet');
?>
<div class="coupon-info">
    <h4>Κουπόνι</h4>
    <ul class="unstyled">
<?php
    echo "<li>Τίτλος προσφοράς: {$coupon['Offer']['title']}</li>";
    echo "<li>Κωδικός κουπονιού: <span class=\"label label-info\">"
        . "{$coupon['Coupon']['serial_number']}</span></li>";
    echo "<li>Ημ/νία δέσμευσης: {$this->Time->format('d-m-Y H:i',
        $coupon['Coupon']['created'])}</li>";
    echo "<li>Στοιχεία σπουδαστή: {$coupon['Student']['firstname']}"
        . " {$coupon['Student']['lastname']}</li>";
?>
    </ul>
</div>

<p>
    <?php
    $pdf_link = $this->Html->link(
        "<i class='icon-download icon-white'></i> Αρχείο PDF",
        array(
            'controller' => 'coupons',
            'action' => 'pdf',
            $coupon['Coupon']['id']
        ),
        array('class' => 'btn btn-success', 'escape' => false)
    );
    echo $pdf_link;
    ?>
</p>

<div class="company-info">
    <h4>Στοιχεία επιχείρησης</h4>
    <ul class="unstyled">
<?php
    echo "<li>Όνομα: {$coupon['Offer']['Company']['name']}</li>";
    echo "<li>Διεύθυνση: {$coupon['Offer']['Company']['address']}"
        . ", {$coupon['Offer']['Company']['postalcode']}</li>";
    echo "<li>Στοιχεία επικοινωνίας";
        echo "<ul>";
            echo "<li>Τηλ: {$coupon['Offer']['Company']['phone']}</li>";
            echo "<li>Fax: {$coupon['Offer']['Company']['fax']}</li>";
        echo "</ul>";
    echo "</li>";
?>
    </ul>
<?php
    if (isset($coupon['Offer']['Company']['latitude'])
        && isset($coupon['Offer']['Company']['longitude'])) {

        $lat = $coupon['Offer']['Company']['latitude'];
        $lng = $coupon['Offer']['Company']['longitude'];
        $api_key = "6e88be5b35b842dca178fb0beb724a32";
        $images_path = "{$this->webroot}img/";
        $map_width = 400;
        $map_height = 280;
        echo "<br /><div id='map'></div>";
        echo "<script>var map = new L.Map('map');$('#map').css('width',{$map_width}).css('height',{$map_height});";
        echo "var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/{$api_key}/997/256/{z}/{x}/{y}.png';";
        echo "var cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18});";
        echo "var company = new L.LatLng({$lat},{$lng});";
        echo "map.setView(company, 15).addLayer(cloudmade);";
        echo "var MyIcon = L.Icon.extend({iconUrl:'{$images_path}marker.png',";
        echo "shadowUrl:'{$images_path}marker-shadow.png',iconSize:new L.Point(25,41),";
        echo "shadowSize:new L.Point(41,41),iconAnchor: new L.Point(13,21),";
        echo "popupAnchor:new L.Point(-3,-41)});";
        echo "var icon = new MyIcon();";
        echo "var marker = new L.Marker(company,{icon: icon});";
        echo "map.addLayer(marker);</script>";
        echo "<noscript>";
        echo "<img src='http://staticmap.openstreetmap.de/staticmap.php?";
        echo "center={$lat},{$lng}&zoom=15&size={$map_width}x{$map_height}&";
        echo "markers={$lat},{$lng},ol-marker-gold' /><br/>";
        echo "</noscript>";
    }
?>
</div>
