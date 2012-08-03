<?php
    echo $this->Html->css('leaflet');
    echo $this->Html->script('leaflet');
    $role = $this->Session->read('Auth.User.role');

    // all students may only see their own profile
    // admins may see all students proriles
    if ($role == ROLE_STUDENT) {
        if ($user['receive_email']) {
            $message = 'Δεν επιθυμώ να λαμβάνω e-mail με τις νέες προσφορές';
            $action = 'unsubscribe';
        } else {
            $message = 'Επιθυμώ να λαμβάνω e-mail με τις νέες προσφορές';
            $action = 'subscribe';
        }

        $offer_mailing = $this->Html->link($message,
                                         array('controller' => 'students',
                                               'action' => $action),
                                         array('class' => 'btn btn-mini'));
        $offer_mailing = "<p>{$offer_mailing}</p>";
    } else {
        $offer_mailing = '';
    }
?>
<?php
    $geolocation = $this->Session->read('Auth.User.geolocation');

    if (isset($geolocation['lat']) && isset($geolocation['lng'])) {
        echo "<div id='with_map' class='well'>";
        $lat = $geolocation['lat'];
        $lng = $geolocation['lng'];
        $api_key = "6e88be5b35b842dca178fb0beb724a32";
        $images_path = "{$this->webroot}img/";
        $map_width = 360;
        $map_height = 200;
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
        echo "<div id='map'>";
        echo "<img src='http://staticmap.openstreetmap.de/staticmap.php?";
        echo "center={$lat},{$lng}&zoom=15&size={$map_width}x{$map_height}&";
        echo "markers={$lat},{$lng},ol-marker-gold' /><br/>";
        echo "</div>";
        echo "</noscript>";
    } else {
        echo "<div class='well'>";
    }
?>
    <h4>Στοιχεία χρήστη</h4>
    <p><?php echo "{$user['firstname']} {$user['lastname']}";?></p>
    <p><i class="icon-user"></i> <?php echo $user['username'];?></p>
    <p><i class="icon-envelope"></i> <?php echo $user['email'];?></p>
    <?php echo $offer_mailing ?>
</div>

<?php
    // display coupons booked by current user
    $elem_html = $this->element('coupons',
        array('role' => $role, 'view' => 'profile'));
    echo $elem_html;

    // display votes cast by current user
    $elem_html = $this->element('votes');
    echo $elem_html;
