function address_lookup(req_url, req_address) {
    $.ajax(
        {
            type: 'POST',
            url: req_url,
            data: {
                address: req_address
            },
            beforeSend: function() {
                $("#ajax-status").html('<img src="../../img/ajax-loader.gif" alt="searching" />');
            },
            success: function(data) {
                data = JSON.parse(data);
                if (data.lng == null || data.lat == null) {
                    $("#ajax-status").html('<i class="icon-warning-sign"></i>');
                } else {
                    $('#ajax-status').html('<i class="icon-ok"></i>');
                    $('#comp-longitude').val(data.lng);
                    $('#comp-latitude').val(data.lat);
                    // create a new coordinaes object for maps
                    var latlng = new L.LatLng(data.lat, data.lng);
                    // set marker position and update popup based on maker
                    marker.setLatLng(latlng);
                    popup.setLatLng(marker.getLatLng());
                }
            },
            error: function() {
                $("#ajax-status").html('<i class="icon-warning-sign"></i>');
            }
        }
    );
}
