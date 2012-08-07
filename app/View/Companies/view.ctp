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
    $img = "<div class='company-image-block'>";
    foreach ($company['Image'] as $image) {
        $img .= "<div class='image_frame'>";
        $img .= $this->Html->image('/images/thumb/'.$image['Image']['id']);
        $img .= "</div>";
    }
    $img .= "</div>";
    echo $img;
}

echo "<div class='company-info-block'>";
if (isset($comp['address']))
    echo '<span class="bold">Διεύθυνση : </span>'.$comp['address'].'<br/>';

if (isset($comp['municipality']))
    echo '<span class="bold">Δήμος : </span>' .$comp['municipality'].'<br />';

if (isset($comp['postalcode']))
    echo '<span class="bold">Ταχ. Κώδικας : </span>'.$comp['postalcode'].'<br/>';

if (isset($company['User']['email']))
    echo '<span class="bold">Email : </span>'.$company['User']['email'].'<br/>';

if (isset($comp['phone']))
    echo '<span class="bold">Τηλέφωνο : </span>'.$comp['phone'].'<br/>';

if (isset($comp['fax']))
    echo '<span class="bold">Φαξ : </span>'.$comp['fax'].'<br/>';

if (isset($comp['service_type']))
    echo '<span class="bold">Είδος υπηρεσιών : </span>'.$comp['service_type'].'<br/>';

if (isset($comp['afm']))
    echo '<span class="bold">ΑΦΜ :</span> '.$comp['afm'].'<br/>';

if (! empty($company['WorkHour'])) {
    echo '<span class="bold">Ωράριο λειτουργίας επιχείρησης</span><br/>';
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
echo "</div>";

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
    // setup table headers
    ?>
    <div class='company-table'>
    <table class="table table-striped">
    <thead>
        <tr>
            <th>Ψήφοι</th>
            <th>Προσφορά</th>
            <th>Τύπος</th>
            <?php
            // offer actions are only available on admin and offer owner
            // so adjust table headers accordingly
            if (($this->Session->read('Auth.User.id') == $comp['user_id'])
                || ($this->Session->read('Auth.User.role') === ROLE_ADMIN)) {
                    echo '<th>Ενέργειες</th>';
                }
            ?>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach ($company['Offer']['Active'] as $active) {
        echo '<tr>';
        $vote_plus = $active['vote_plus'];
        $vote_minus = $active['vote_minus'];
        $vote_count = $active['vote_count'];
        $votes = "<span class='votes green'>+{$vote_plus}</span> ";
        $votes .= "<span class='votes red'>-{$vote_minus}</span> ";
        $votes .= "({$vote_count}) ";
        echo "<td>{$votes}</td>";

        $offer_link = $this->Html->link($active['title'],
                               array('controller' => 'offers',
                                     'action' => 'view', $active['id'])
                                 );

        // clear offer actions string here
        $offer_actions = '';

        // append clock icon to offer
        if ($is_user_the_owner) {
            $time_end = new DateTime($active['autoend']);
            if ($time_end > $time_now) {
                $offer_link .= "&nbsp;".$html_clock;
            }


            // setup offer actions
            // start/end or flag spam if viewer is admin
            $offer_actions .= $this->Html->link(
            'Τερματισμός',
            array(
                'controller' => 'offers',
                'action' => 'terminate',
                $active['id']),
            array('class' => 'btn btn-mini btn-danger'),
            'Ο τερματισμός μίας προσφοράς δεν μπορεί να αναιρεθεί. '.
            'Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
        }

        // show offer link
        echo "<td>{$offer_link}</td>";

        // build offer type string with approproate color code
        echo "<td>{$this->CouponsLayout->offer_label($active['offer_type_id'])}</td>";

        if ($is_user_admin) {
            $offer_actions .= $this->Html->link(
                    $flag_icon . ' Ανάρμοστη',
                    array('controller' => 'offers',
                          'action' => 'improper',
                           $active['id']),
                    array('escape' => false,
                          'class' => 'btn btn-mini btn-danger')
            );
        }

        // check if we have available actions and show them
        if (! empty($offer_actions)) {
            echo "<td>{$offer_actions}</td>";
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
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

            $offer_actions = array();

            // title
            $offer_link = $this->Html->link($draft['title'], array(
                'controller' => 'offers',
                'action' => 'view', $draft['id'])
            );

            if ($is_user_the_owner) {
                // display a clock next to offer if autostart time is set
                $time_start = new DateTime($draft['autostart']);
                if ($time_start > $time_now) {
                    $offer_link .= "&nbsp;".$html_clock;
                }

                // enable action
                $offer_actions[] = $this->Html->link(
                    'Ενεργοποίηση',
                    array(
                        'controller' => 'offers',
                        'action' => 'activate',
                        $draft['id']),
                    array('class' => 'btn btn-mini btn-success'),
                    'Οι ενεργοποιημένες προσφορές δεν είναι δυνατό να τροποποιηθούν.'
                    .'Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');

                // edit action
                $offer_actions[] = $this->Html->link(
                    'Επεξεργασία',
                    array(
                        'controller' => 'offers',
                        'action' => 'edit',
                        $draft['id']),
                    array('class' => 'btn btn-mini btn-info'));

                // copy action
                $offer_actions[] = $this->Html->link(
                    'Αντιγραφή',
                    array(
                        'controller' => 'offers',
                        'action' => 'copy',
                        $draft['id']),
                    array('class' => 'btn btn-mini btn-info'));

                // image edit action
                $offer_actions[] = $this->Html->link(
                    'Φωτογραφίες',
                    array(
                        'controller' => 'offers',
                        'action' => 'imageedit',
                        $draft['id']),
                    array('class' => 'btn btn-mini btn-info'),
                    'Οι ενεργοποιημένες προσφορές δεν είναι δυνατό να τροποποιηθούν.'
                    .'Είστε βέβαιοι ότι θέλετε να συνεχίσετε;');
            }

            // show offer link
            echo $offer_link;

            // check if we have available actions and show them
            if (! empty($offer_actions)) {
                foreach($offer_actions as $action) {
                    echo "<td>{$action}&nbsp;</td>";
                }
            }
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
