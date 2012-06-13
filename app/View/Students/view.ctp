<?php
    echo $this->Html->css('leaflet');
    echo $this->Html->script('leaflet');
?>
<div class="well">
    <h4>Στοιχεία χρήστη</h4>
    <p><?php echo "{$user['firstname']} {$user['lastname']}";?></p>
    <p><i class="icon-user"></i> <?php echo $user['username'];?></p>
    <p><i class="icon-envelope"></i> <?php echo $user['email'];?></p>
<?php
    $geolocation = $this->Session->read('Auth.User.geolocation');

    if (isset($geolocation['lat']) && isset($geolocation['lng'])) {
        $lat = $geolocation['lat'];
        $lng = $geolocation['lng'];
        $api_key = "6e88be5b35b842dca178fb0beb724a32";
        $images_path = "{$this->webroot}img/";
        $map_width = 400;
        $map_height = 280;
        echo "<div id='map'></div>";
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

<div class="well">
    <h4>Κουπόνια</h4>
    <br />
    <table class="table table-condensed table-striped">
        <thead>
            <tr>
                <th>Τίτλος προσφοράς</th>
                <th>Εταιρία</th>
                <th>Κωδικός κουπονιού</th>
                <th>Ημ/νία δέσμευσης</th>
                <th>Διαγραφή</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($coupons as $c) {
                    // if we use it more than one time put it here
                    // to avoid bloated code!
                    $date = $c['Coupon']['created'];
                    $title = $c['Offer']['title'];
                    $serial_number = $c['Coupon']['serial_number'];

                    // build links
                    $offer_link = $this->Html->link(
                        $title,
                        array(
                            'controller' => 'offers',
                            'action' => 'view',
                            $c['Offer']['id']
                        ),
                        array()
                    );

                    $company_link = $this->Html->link(
                        $c['Offer']['Company']['name'],
                        array(
                            'controller' => 'companies',
                            'action' => 'view',
                            $c['Offer']['company_id']
                        ),
                        array()
                    );

                    $coupon_link = $this->Html->link(
                        $serial_number,
                        array(
                            'controller' => 'coupons',
                            'action' => 'view',
                            $c['Coupon']['id']
                        ),
                        array()
                    );

                    $delete_link = $this->Html->link(
                        "delete",
                        array(
                            'controller' => 'coupons',
                            'action' => 'delete',
                            $c['Coupon']['id']
                        ),
                        array(),
                        "Αυτή η ενέργεια δεν μπορεί να αναιρεθεί"
                    );

                    echo "<tr>";

                    // offer title - append "spam" keyword on spam
                    echo "<td>";
                    if ($c['Offer']['is_spam']) {
                        echo '<span class="label label-important">spam</span> ';
                        echo $title;
                    }
                    else {
                        echo $offer_link;
                    }
                    echo"</td>";

                    echo "<td>{$company_link}</td>";

                    // coupon link - don't allow coupon view if spam
                    echo "<td>";
                    if ($c['Offer']['is_spam']) {
                        echo $serial_number;
                    }
                    else {
                        echo $coupon_link;
                    }
                    echo"</td>";

                    echo "<td>{$this->Time->format('d-m-Y',$date)}</td>";

                    // show delete link only for finished offers
                    if ($c['Offer']['ended'] == true) {
                        echo "<td>{$delete_link}</td>";
                    } else {
                        $delete = "<td title=\"μόνο κουπόνια από μη ενεργές";
                        $delete .=" προσφορές μπορούν να διαγραφούν\"";
                        $delete .= "class=\"help-text\">διαγραφή</td>";
                        echo $delete;
                    }
                    // to add trash icon use: <i class=\"icon-trash\"></i>
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
</div>
