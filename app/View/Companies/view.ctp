<h6>Ανάρτηση Προσφοράς</h6>
<?php
echo $this->Html->css('leaflet');
echo $this->Html->script('leaflet');
$comp = $company['Company'];
$is_user_the_owner = $this->Session->read('Auth.User.id') == $comp['user_id'];
$is_user_admin = $this->Session->read('Auth.User.role') == ROLE_ADMIN;

if ($is_user_admin) {
        if ($comp['is_enabled']) {
            $enabled_title = "[Απενεργοποίηση]";
            $enabled_action = 'disable';
        } else {
            $enabled_title = "[Ενεργοποίηση]";
            $enabled_action = 'enable';
        }

        $html = $this->Html->link($enabled_title, array(
            'controller' => 'companies',
            'action' => $enabled_action,
            $comp['id']));
        $html .= '<br>';
        echo $html;
}

if ($is_user_the_owner) {
    echo "<p>";
    echo $this->Html->link(offer_type(1), array(
        'controller' => 'offers',
        'action' => 'add_happyhour'),
        array('class' => 'label label-info'));
    echo $this->Html->link(offer_type(2), array(
        'controller' => 'offers',
        'action' => 'add_coupons'),
        array('class' => 'label label-warning'));
    echo $this->Html->link(offer_type(3), array(
        'controller' => 'offers',
        'action' => 'add_limited'),
        array('class' => 'label label-success'));
    echo "</p><h6>";

    echo $this->Html->link('Επεξεργασία στοιχείων επιχείρησης',
                           array('controller' => 'companies',
                           'action' => 'edit',
                           $company['Company']['id']));
    echo "</h6>";
    echo "<h6>";
    echo $this->Html->link('Επεξεργασία φωτογραφιών επιχείρησης',
                           array('controller' => 'companies',
                           'action' => 'imageedit',
                           $company['Company']['id']));
    echo "</h6><br/>";
}

echo 'Επιχείρηση '.$comp['id'].'<br/>';

if (isset($comp['name']))
    echo 'Όνομα επιχείρησης : '.$comp['name'].'<br/>';

if (isset($comp['address']))
    echo 'Διεύθυνση : '.$comp['address'].'<br/>';

if (isset($comp['postalcode']))
    echo 'Ταχ. Κώδικας : '.$comp['postalcode'].'<br/>';

if (isset($company['User']['email']))
    echo 'Email : '.$company['User']['email'].'<br/>';

if (isset($comp['phone']))
    echo 'Τηλέφωνο : '.$comp['phone'].'<br/>';

if (isset($comp['fax']))
    echo 'Φαξ : '.$comp['fax'].'<br/>';

if (isset($comp['service_type']))
    echo 'Είδος υπηρεσιών : '.$comp['service_type'].'<br/>';

if (isset($comp['afm']))
    echo 'ΑΦΜ : '.$comp['afm'].'<br/>';

if (isset($comp['working_hours']))
    echo 'Ωράριο λειτουργίας : '.$comp['working_hours'].'<br/>';
/*
foreach ($company['Image'] as $image) {
    echo $this->Html->image('/images/view/'.$image['id']).'<br/>';
}
*/

if (isset($comp['latitude']) && isset($comp['longitude'])) {
    $lat = $comp['latitude'];
    $lng = $comp['longitude'];
    $api_key = "6e88be5b35b842dca178fb0beb724a32";
    $images_path = "/coupons/img/";
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

echo '<br/>';

// display Active offers
if (empty($company['Offer']['Active'])) {
    echo 'Δεν υπάρχουν ενεργές προσφορές.<br/>';
} else {
    echo 'Ενεργές προσφορές:<br/>';
    foreach ($company['Offer']['Active'] as $active) {
        $vote_class = ($active['vote_count'] >= 0)?'green':'red';
        $votes = "<span class='votes {$vote_class}'>{$active['vote_count']} </span>";
        echo $votes;
        echo $this->Html->link($active['title'],
                               array('controller' => 'offers',
                                     'action' => 'view', $active['id'])
                              );
        if ($active['is_spam'] == TRUE) {
            echo ' [SPAM]';
        }

        if ($is_user_the_owner) {
          echo ' ' . $this->Html->link(
              '[Τερματισμός]',
              array(
                  'controller' => 'offers',
                  'action' => 'terminate',
                  $active['id']),
              null,
              'Ο τερματισμός μίας προσφοράς δεν μπορεί να αναιρεθεί. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
      }

      echo '<br/>';
    }
}

// display Drafts only for the owner of this company and admins
if (($this->Session->read('Auth.User.id') == $comp['user_id'])
    || ($this->Session->read('Auth.User.role') === ROLE_ADMIN)) {
    if (empty($company['Offer']['Draft'])) {
        echo 'Δεν υπάρχουν μη ενεργοποιημένες προσφορές.<br/>';
    } else {
        echo 'Μη ενεργοποιημένες προσφορές:<br/>';
        foreach ($company['Offer']['Draft'] as $draft) {
            $vote_class = ($draft['vote_count'] >= 0)?'green':'red';
            $votes = "<span class='votes {$vote_class}'>{$draft['vote_count']} </span>";
            echo $votes;
            echo $this->Html->link($draft['title'],
                                   array('controller' => 'offers',
                                         'action' => 'view', $draft['id'])
                                  );
            if ($draft['is_spam'] == TRUE) {
                echo ' [SPAM]';
            }

            if ($is_user_the_owner) {
              echo ' ' . $this->Html->link(
                  '[Ενεργοποίηση]',
                  array(
                      'controller' => 'offers',
                      'action' => 'activate',
                      $draft['id']),
                  null,
                  'Οι ενεργοποιημένες προσφορές δε δύναται να τροποποιηθούν. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
            }
            echo '<br/>';
        }
    }
}

// display Inactive offers
if (empty($company['Offer']['Inactive'])) {
    echo 'Δεν υπάρχουν παλαιότερες προσφορές.<br/>';
} else {
    echo 'Παλαιότερες προσφορές:<br/>';
    foreach ($company['Offer']['Inactive'] as $inactive) {
        $vote_class = ($inactive['vote_count'] >= 0)?'green':'red';
        $votes = "<span class='votes {$vote_class}'>{$inactive['vote_count']} </span>";
        echo $votes;
        echo $this->Html->link($inactive['title'],
                               array('controller' => 'offers',
                                     'action' => 'view', $inactive['id'])
                              );
        if ($inactive['is_spam'] == true) {
            echo ' [SPAM]';
        }
        echo '<br/>';
    }
}
