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
<div class="well">
    <h4>Στοιχεία χρήστη</h4>
    <p><?php echo "{$user['firstname']} {$user['lastname']}";?></p>
    <p><i class="icon-user"></i> <?php echo $user['username'];?></p>
    <p><i class="icon-envelope"></i> <?php echo $user['email'];?></p>
    <?php echo $offer_mailing ?>

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
                <?php
                    if ($role === ROLE_STUDENT) {
                        echo "<th>Διαγραφή</th>\n";
                        echo "<th>Download</th>\n";
                    }
                ?>
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

                    if ($role === ROLE_STUDENT) {
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
                    }

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
                    if (($c['Offer']['is_spam']) || ($role !== ROLE_STUDENT)) {
                        echo $serial_number;
                    }
                    else {
                        echo $coupon_link;
                    }
                    echo"</td>";

                    echo "<td>{$this->Time->format('d-m-Y',$date)}</td>";

                    // show delete link only for finished offers
                    if ($role === ROLE_STUDENT) {
                        if ($c['Offer']['ended'] == true) {
                            echo "<td>{$delete_link}</td>";
                        } else {
                            $delete = "<td title='μόνο κουπόνια από μη ενεργές";
                            $delete .= "προσφορές μπορούν να διαγραφούν'";
                            $delete .= "class='help-text'>διαγραφή</td>";
                            echo $delete;
                        }
                        $pdf_link = $this->Html->link(
                            "pdf",
                            array(
                                'controller' => 'coupons',
                                'action' => 'pdf',
                                $c['Coupon']['id']
                            )
                        );
                        echo "<td>{$pdf_link}</td>";
                    }
                    // to add trash icon use: <i class=\"icon-trash\"></i>
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
</div>

<div class="well">
    <h4>Προσφορές που έχω ψηφίσει</h4>
    <br />
    <table class="table table-condensed table-striped">
        <thead>
            <tr>
                <th>Τίτλος προσφοράς</th>
                <th>Συνολικοί Ψήφοι</th>
                <th>
                <?php
                // this header is a link that sorts voted offers

                // default votes order
                $order = 'down';
                // set order for url based on previous selection
                if (isset($this->params['named']['order'])) {
                    if ($this->params['named']['order'] == 'down') {
                        $order = 'up';
                    }
                }
                // sort url for votes
                echo $this->Html->link('Η ψήφος μου', array(
                    'controller' => 'students',
                    'action' => 'view',
                    'order' => $order),
                    array()
                );
                ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach($voted_offers as $offer) {
            echo '<tr>';
            // offer title
            echo '<td>';
            echo $this->Html->link($offer['Offer']['title'], array(
                'controller' => 'offers',
                'action' => 'view',
                $offer['Offer']['id']
                ),
                array()
            );
            echo '</td>';

            // all votes
            echo '<td class="profile-votes">';
            $offer_votes = "<ul><li><span class='green'>+{$offer['Offer']['vote_plus']}</span></li>";
            $offer_votes .= "<li><span class='red'>-{$offer['Offer']['vote_minus']}</span></li>";
            $offer_votes .= "<li>({$offer['Offer']['vote_count']})</li></ul>";
            echo $offer_votes;

            echo '</td>';

            // student vote + vote controls
            echo '<td class="profile-votes">';
            $vote_class = ($offer['Vote']['vote'])?'green':'red';
            $my_vote = ($offer['Vote']['vote'])?'+1':'-1';
            //$vote_elemnts = "<div class='{$vote_class}'>{$my_vote}</div>";
            $vote_elements = "<ul><li class='{$vote_class}'>{$my_vote}</li>";

            if ($this->Session->read('Auth.User.role') === ROLE_STUDENT) {
                $icon_thumbs_up = "<i class='icon-thumbs-up'></i>";
                $icon_thumbs_down = "<i class='icon-thumbs-down'></i>";
                $icon_cancel = "<i class='icon-remove'></i>";
                $link_up = $this->Html->link($icon_thumbs_up,
                    array('controller' => 'votes', 'action' => 'vote_up', $offer['Offer']['id']),
                    array('escape' => false));
                $link_down = $this->Html->link($icon_thumbs_down,
                    array('controller' => 'votes', 'action' => 'vote_down', $offer['Offer']['id']),
                    array('escape' => false));
                $link_cancel = $this->Html->link($icon_cancel,
                    array('controller' => 'votes', 'action' => 'vote_cancel', $offer['Offer']['id']),
                    array('escape' => false));
                //echo "<p>{$link_up} {$link_down} {$link_cancel}</p>";
                $vote_elements .= "<li>{$link_up}</li><li>{$link_down}</li><li>{$link_cancel}</li>";
            }
            // close and print list
            $vote_elements .= '</ul>';
            echo $vote_elements;

            echo '</td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</div>
