<?php
echo $this->Html->css('leaflet');
echo $this->Html->script('leaflet');

echo $this->Form->create(false, array(
                                    'url' => array(
                                        'controller' => 'companies',
                                        'action' => 'edit',
                                        $company['Company']['id']
                                    ),
                                    'type' => 'POST',
                                    'enctype' => 'multipart/form-data',
                                 ));

echo $this->Form->input('Company.name', array(
                        'label' => 'Όνομα επιχείρησης',
                        'class' => 'span4',
                        'type'  => 'text'
                       ));
echo $this->Form->input('Company.service_type', array(
                        'label' => 'Προσφερόμενη υπηρεσία',
                        'class' => 'span4',
                        'type'  => 'text'
                       ));
echo $this->Form->input('Company.municipality_id', array(
                        'label' => 'Δήμος',
                        'type'  => 'select'
                       ));
echo $this->Form->input('Company.address', array(
                        'label' => 'Διεύθυνση',
                        'class' => 'span4',
                        'type'  => 'text',
                       ));
echo $this->Form->input('Company.postalcode', array(
                        'label' => 'Ταχυδρομικός κώδικας',
                        'class' => 'span1',
                        'type'  => 'text'
                       ));
echo $this->Form->input('User.email', array(
                        'label' => 'E-mail',
                        'class' => 'span4',
                        'type'  => 'text'
                       ));
echo $this->Form->input('Company.phone', array(
                        'label' => 'Τηλέφωνο',
                        'class' => 'span2',
                        'type'  => 'text'
                       ));
echo $this->Form->input('Company.fax', array(
                        'label' => 'Fax',
                        'class' => 'span2',
                        'type'  => 'text'
                       ));

echo $this->Form->input('Company.longitude', array(
                        'id' => 'comp-longitude',
                        'type'  => 'hidden'
                       ));

echo $this->Form->input('Company.latitude', array(
                        'id' => 'comp-latitude',
                        'type'  => 'hidden'
                       ));

echo $this->element( "work_hours", array(
    "work_hour_count" => $company['Company']['work_hour_count'],
    "timeFormat" => 24,
    //"interval" => 15 //default
    "header" => array('Ημέρα', 'Ώρα Έναρξης', 'Ώρα Λήξης', 'Επιλογή'),
    "table_class" => "table table-striped",
    //"input_class" => span3, //default
    //"input_label" => null, //default
    //"staring_time_label" => null, //default
    //"ending_time_lebel" => null, //default
) );

echo $this->Form->hidden('User.id');
echo $this->Form->hidden('Company.id');
echo $this->Form->hidden('Company.user_id');
echo $this->Form->hidden('Company.afm');
echo $this->Form->end('Αποθήκευση');
echo $this->Html->link('Επιστροφή', array(
                       'controller' => 'companies',
                       'action' => 'view',
                       $company['Company']['id']));


$comp = $company['Company'];

if (isset($comp['latitude']) && isset($comp['longitude'])) {
    $lat = $comp['latitude'];
    $lng = $comp['longitude'];
} else {
    $lat = '37.97';
    $lng = '23.73';
}
$api_key = "6e88be5b35b842dca178fb0beb724a32";
$images_path = "/coupons/img/";
$map_width = 400;
$map_height = 280;

echo <<< __EOF__
<br /><div id='map'></div>
<script type="text/javascript">
    // map callback functions
    function onMarkerClick(e) {
        map.openPopup(popup);
    };

    function onMarkerDragEnd(e) {
        popup.setLatLng(marker.getLatLng());
        $('#comp-longitude').val(marker.getLatLng()['lat']);
        $('#comp-latitude').val(marker.getLatLng()['lng']);
    }

    // show map
    var map = new L.Map('map');$('#map').css('width',{$map_width}).css('height',{$map_height});
    var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/{$api_key}/997/256/{z}/{x}/{y}.png';
    var cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18});
    var company = new L.LatLng({$lat},{$lng});
    map.setView(company, 15).addLayer(cloudmade);

    // show marker
    var MyIcon = L.Icon.extend({iconUrl:'{$images_path}marker.png',
        shadowUrl:'{$images_path}marker-shadow.png',iconSize:new L.Point(25,41),
        shadowSize:new L.Point(41,41),iconAnchor: new L.Point(13,21),
        popupAnchor:new L.Point(-3,-41)});
    var icon = new MyIcon();
    var marker = new L.Marker(company, {icon: icon, draggable: true});
    map.addLayer(marker);

    // show popup
    var popup = new L.Popup({maxWidth: 400, offset: new L.Point(0, -20)});
    popup.setLatLng(new L.LatLng({$lat}, ${lng}));
    popup.setContent("Σύρετε τον δείκτη για να καταδείξετε την επιχείρησή σας.");
    map.openPopup(popup);
    marker.on('click', onMarkerClick);
    marker.on('dragend', onMarkerDragEnd);
</script>
__EOF__;
