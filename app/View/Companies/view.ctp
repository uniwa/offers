<?php
echo $this->Html->css('leaflet');
echo $this->Html->script('leaflet');
$comp = $company['Company'];
$is_user_the_owner = $this->Session->read('Auth.User.id') == $comp['user_id'];
$is_user_admin = $this->Session->read('Auth.User.role') == ROLE_ADMIN;

if (isset($comp['latitude']) && isset($comp['longitude'])) {
    $lat = $comp['latitude'];
    $lng = $comp['longitude'];
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

if ($is_user_admin) {
    $flag_icon = $this->Html->tag('i', '', array('class' => 'icon-flag'));
}

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

// show company name
echo "<div class='bold company-name'>{$comp['name']}</div>";

if ($is_user_the_owner) {
    // offer actions
    echo "<div class='controls-block'>";
        echo "<h6>ΑΝΑΡΤΗΣΗ ΠΡΟΣΦΟΡΑΣ</h6>";
        echo "<ul class='unstyled inline-list company-btn'>";
        echo "<li>";
        echo $this->Html->link(offer_type(1), array(
            'controller' => 'offers',
            'action' => 'add_happyhour'),
            array('class' => 'btn btn-primary'));
        echo "</li>";
        echo "<li>";
        echo $this->Html->link(offer_type(2), array(
            'controller' => 'offers',
            'action' => 'add_coupons'),
            array('class' => 'btn btn-warning'));
        echo "</li>";
        echo "<li>";
        echo $this->Html->link(offer_type(3), array(
            'controller' => 'offers',
            'action' => 'add_limited'),
            array('class' => 'btn btn-success'));
        echo "</ul>";
    echo "</div>";

    // edit actions
    echo "<div class='controls-block'>";
        echo "<h6>ΠΡΟΦΙΛ</h6>";
        echo "<ul class='unstyled inline-list company-btn'>";
        echo "<li>";
        echo $this->Html->link('Επεξεργασία στοιχείων',
                               array('controller' => 'companies',
                               'action' => 'edit',
                               $company['Company']['id']),
                                array('class' => 'btn btn-inverse'));
        echo "</li>";
        echo "<li>";
        echo $this->Html->link('Επεξεργασία φωτογραφιών',
                               array('controller' => 'companies',
                               'action' => 'imageedit',
                               $company['Company']['id']),
                                array('class' => 'btn btn-inverse'));
        echo "</li>";
        echo "</ul>";
    echo "</div>";
}

if (! empty($company['Image']) ) {
    $img = "";
    foreach ($company['Image'] as $image) {
        $img .= "<div class='image_frame'>";
        $img .= $this->Html->image('/images/thumb/'.$image['Image']['id']);
        $img .= "</div>";
    }
    echo $img;
}

if (isset($comp['address']))
    echo 'Διεύθυνση : '.$comp['address'].'<br/>';

if (isset($comp['municipality']))
    echo 'Δήμος : ' .$comp['municipality'].'<br />';

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

if (! empty($company['WorkHour'])) {
    echo '<ul>';
    foreach($company['WorkHour'] as $wh) {
        echo "<li><span class=\"bold\">{$wh['name']}:</span> {$wh['time1']}";
        if (! empty($wh['time2'])) {
            echo "<span class=\"bold\"> και</span> {$wh['time2']}</li>";
        } else {
            echo "</li>";
        }
    }
    echo '</ul>';
}
echo '<br/>';


// ----------------------------------------------------------------------------
// Bootstrap togglable tab
// ----------------------------------------------------------------------------
// @see: http://twitter.github.com/bootstrap/javascript.html#tabs

// tab element
?>
<ul class="nav nav-tabs" id="companyTab">
<li class="active">
    <a href="#offers-active" data-toggle="tab">Ενεργές Προσφορές</a>
</li>
<?php
if (($this->Session->read('Auth.User.id') == $comp['user_id'])
    || ($this->Session->read('Auth.User.role') === ROLE_ADMIN)) {
    echo '<li><a href="#offers-inactive" data-toggle="tab">Ανενεργές Προσφορές</a></li>';
}
?>
<li>
    <a href="#offers-old" data-toggle="tab">Παλαιότερες Προσφορές</a>
</li>
<?php
if ($this->Session->read('Auth.User.id') == $comp['user_id']) {
    echo '<li><a href="#stats" data-toggle="tab">Επισκεψιμότητα</a></li>';
}
?>
</ul>

<?php
// everthing below belong inside the togglable tab
echo '<div class="tab-content">';
//--

// display total stats for all offers
// only if visitor = owner and offer type = coupons
if ($this->Session->read('Auth.User.id') == $comp['user_id']) {
    $html_stats = '<div class="tab-pane" id="stats">';
    $html_stats .= "<p><strong>Σύνολο επισκέψεων για όλες τις προσφορές:";
    $html_stats .= "</strong> {$visits['total']}<br />";
    $html_stats .= "<strong>Σύνολο μοναδικών επισκεπτών (βάσει IP) για όλες τις προσφορές:";
    $html_stats .= "</strong> {$visits['unique']}</p><br />";
    $html_stats .= "</div>";
    echo $html_stats;
}

$html_clock = "<i class='icon-time'></i>";

// get the current datetime
$time_now = new DateTime();

// display Active offers
echo '<div class="tab-pane active" id="offers-active">'; // attach this content to Tab

if (empty($company['Offer']['Active'])) {
    echo 'Δεν υπάρχουν ενεργές προσφορές.<br/>';
} else {
    foreach ($company['Offer']['Active'] as $active) {
        $vote_plus = $active['vote_plus'];
        $vote_minus = $active['vote_minus'];
        $vote_count = $active['vote_count'];
        $votes = "<span class='votes green'>+{$vote_plus}</span> ";
        $votes .= "<span class='votes red'>-{$vote_minus}</span> ";
        $votes .= "({$vote_count}) ";
        echo $votes;

        echo $this->Html->link($active['title'],
                               array('controller' => 'offers',
                                     'action' => 'view', $active['id'])
                              );

        if ($is_user_the_owner) {
            // display a clock next to offer if autoend time is set
            $time_end = new DateTime($active['autoend']);
            if ($time_end > $time_now) {
                echo $html_clock;
            }

            echo ' ' . $this->Html->link(
            '[Τερματισμός]',
            array(
                'controller' => 'offers',
                'action' => 'terminate',
                $active['id']),
            null,
            'Ο τερματισμός μίας προσφοράς δεν μπορεί να αναιρεθεί. '.
            'Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
        } else if ($is_user_admin) {
            echo $this->Html->link(
                    $flag_icon . ' Ανάρμοστη',
                    array('controller' => 'offers',
                          'action' => 'improper',
                           $active['id']),
                    array('escape' => false,
                          'class' => 'btn btn-mini')
            );
        }

      echo '<br/>';
    }
}
// end block that defines tab contents for id: offers-active
echo '</div>';


// display Drafts only for the owner of this company and admins
if (($this->Session->read('Auth.User.id') == $comp['user_id'])
    || ($this->Session->read('Auth.User.role') === ROLE_ADMIN)) {

    // start block that defines tab contents for id: offers-inactive
    echo '<div class="tab-pane" id="offers-inactive">';

    if (empty($company['Offer']['Draft'])) {
        echo 'Δεν υπάρχουν μη ενεργοποιημένες προσφορές.<br/>';
    } else {
        foreach ($company['Offer']['Draft'] as $draft) {
            $vote_plus = $draft['vote_plus'];
            $vote_minus = $draft['vote_minus'];
            $vote_count = $draft['vote_count'];
            $votes = "<span class='votes green'>+{$vote_plus}</span> ";
            $votes .= "<span class='votes red'>-{$vote_minus}</span> ";
            $votes .= "({$vote_count}) ";
            echo $votes;

            echo $this->Html->link($draft['title'],
                                   array('controller' => 'offers',
                                         'action' => 'view', $draft['id'])
                                  );

            if ($is_user_the_owner) {
                // display a clock next to offer if autostart time is set
                $time_start = new DateTime($draft['autostart']);
                if ($time_start > $time_now) {
                    echo $html_clock;
                }

                echo ' ' . $this->Html->link(
                '[Ενεργοποίηση]',
                array(
                    'controller' => 'offers',
                    'action' => 'activate',
                    $draft['id']),
                null,
                'Οι ενεργοποιημένες προσφορές δεν είναι δυνατό να τροποποιηθούν. Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
            }
            echo '<br/>';
        }
    }
    // end block that defines tab contents for id: offers-inactive
    echo '</div>';
}

// tag that creates the spam notification
$spam_tag_title = _('Η προσφορά έχει χαρακτηριστεί ανάρμοστη από διαχειριστή του συστήματος');
$spam_tag_options = array('class' => 'label label-important',
                          'title' => $spam_tag_title);

$spam_tag = $this->Html->tag('span', 'ΑΝΑΡΜΟΣΤΗ', $spam_tag_options);


// start block that defines tab contents for id: offers-old
echo '<div class="tab-pane" id="offers-old">';


// display Inactive offers
if (empty($company['Offer']['Inactive'])) {
    echo 'Δεν υπάρχουν παλαιότερες προσφορές.<br/>';
} else {
    foreach ($company['Offer']['Inactive'] as $inactive) {
        $vote_plus = $inactive['vote_plus'];
        $vote_minus = $inactive['vote_minus'];
        $vote_count = $inactive['vote_count'];
        $votes = "<span class='votes green'>+{$vote_plus}</span> ";
        $votes .= "<span class='votes red'>-{$vote_minus}</span> ";
        $votes .= "({$vote_count}) ";

        if ($inactive['is_spam']) {

            echo $spam_tag;

            // in case of a flagged (as spam) offer, link its title to its view
            // iff authed user is either the owner or an admin
            $should_link_title = $is_user_the_owner || $is_user_admin;

        } else {

            $should_link_title = true;
            if ($is_user_admin) {

                // offer a link to flag the offer as spam
                $spamify = $this->Html->link(
                    $flag_icon . ' Ανάρμοστη',
                    array('controller' => 'offers',
                          'action' => 'improper',
                           $inactive['id']),
                    array('escape' => false,
                          'class' => 'btn btn-mini')
            );
            }
        }

        echo $votes;

        if ($should_link_title) {
            echo $this->Html->link($inactive['title'],
                                   array('controller' => 'offers',
                                         'action' => 'view', $inactive['id'])
            );
        } else {
            echo $inactive['title'];
        }

        if (isset($spamify)) {
            echo $spamify;
            unset($spamify);
        }
        echo '<br/>';
    }
}

// end block that defines tab contents for id: offers-old
echo '</div>';

// close div defining table content boundaries
echo '</div>';

// ----------------------------------------------------------------------------
// Bootstrap togglable tab end
// ----------------------------------------------------------------------------
